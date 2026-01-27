<?php

use App\Enums\UserAccountRole;
use App\Mail\InvitationMail;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('allows owner to create invitation with valid email and role', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $account = createAccount();
    $inviteEmail = 'newuser@example.com';

    actingAsAccount($owner, $account)
        ->from(route('users'))
        ->followingRedirects()
        ->post(route('user-invitations.store'), [
            'email' => $inviteEmail,
            'role' => 'user',
        ])
        ->assertSuccessful();

    $invitation = UserInvitation::query()
        ->where('email', $inviteEmail)
        ->where('account_id', $account->id)
        ->first();

    expect($invitation)
        ->not->toBeNull()
        ->email->toBe($inviteEmail)
        ->role->toBe(UserAccountRole::User)
        ->expires_at->not->toBeNull()
        ->accepted_at->toBeNull()
        ->and($invitation->account)
        ->is($account)->toBeTrue()
        ->and($invitation->invitedBy)
        ->is($owner)->toBeTrue();

    Mail::assertQueued(InvitationMail::class, fn (InvitationMail $mail) => $mail->invitation->is($invitation));
});

it('creates invitation with owner role when specified', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $account = createAccount();
    $inviteEmail = 'newowner@example.com';

    actingAsAccount($owner, $account)
        ->from(route('users'))
        ->followingRedirects()
        ->post(route('user-invitations.store'), [
            'email' => $inviteEmail,
            'role' => 'owner',
        ])
        ->assertSuccessful();

    $invitation = UserInvitation::query()
        ->where('email', $inviteEmail)
        ->where('account_id', $account->id)
        ->first();

    expect($invitation)
        ->role->toBe(UserAccountRole::Owner);
});

it('forbids non-owner from creating invitation', function () {
    actingAsAccount(role: UserAccountRole::User)
        ->post(route('user-invitations.store'), [
            'email' => 'newuser@example.com',
            'role' => 'user',
        ])
        ->assertForbidden();
});

it('requires email field', function () {
    actingAsAccount()
        ->from(route('users'))
        ->post(route('user-invitations.store'), [
            'role' => 'user',
        ])
        ->assertInvalid(['email']);
});

it('requires valid email format', function () {
    actingAsAccount()
        ->from(route('users'))
        ->post(route('user-invitations.store'), [
            'email' => 'invalid-email',
            'role' => 'user',
        ])
        ->assertInvalid(['email']);
});

it('requires email to not exceed max length', function () {
    actingAsAccount()
        ->from(route('users'))
        ->post(route('user-invitations.store'), [
            'email' => Str::random(181).'@example.com',
            'role' => 'user',
        ])
        ->assertInvalid(['email']);
});

it('requires role field', function () {
    actingAsAccount()
        ->from(route('users'))
        ->post(route('user-invitations.store'), [
            'email' => 'newuser@example.com',
        ])
        ->assertInvalid(['role']);
});

it('requires valid role value', function () {
    actingAsAccount()
        ->from(route('users'))
        ->post(route('user-invitations.store'), [
            'email' => 'newuser@example.com',
            'role' => 'invalid-role',
        ])
        ->assertInvalid(['role']);
});

it('prevents inviting user who already has access to account', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $account->users()->attach($existingUser, [
        'role' => UserAccountRole::User->value,
    ]);

    actingAsAccount($owner, $account)
        ->from(route('users'))
        ->post(route('user-invitations.store'), [
            'email' => 'existing@example.com',
            'role' => 'user',
        ])
        ->assertInvalid(['email']);
});

it('prevents inviting user with case-insensitive email match', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    $existingUser = User::factory()->create([
        'email' => 'Existing@Example.com',
    ]);

    $account->users()->attach($existingUser, [
        'role' => UserAccountRole::User->value,
    ]);

    actingAsAccount($owner, $account)
        ->from(route('users'))
        ->post(route('user-invitations.store'), [
            'email' => 'EXISTING@EXAMPLE.COM',
            'role' => 'user',
        ])
        ->assertInvalid(['email']);
});

it('prevents inviting user who already has pending invitation', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    UserInvitation::factory()
        ->for($account)
        ->for($owner, 'invitedBy')
        ->withEmail('pending@example.com')
        ->create();

    actingAsAccount($owner, $account)
        ->from(route('users'))
        ->post(route('user-invitations.store'), [
            'email' => 'pending@example.com',
            'role' => 'user',
        ])
        ->assertInvalid(['email']);
});

it('prevents duplicate pending invitation with case-insensitive email', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    UserInvitation::factory()
        ->for($account)
        ->for($owner, 'invitedBy')
        ->withEmail('Pending@Example.com')
        ->create();

    actingAsAccount($owner, $account)
        ->from(route('users'))
        ->post(route('user-invitations.store'), [
            'email' => 'PENDING@EXAMPLE.COM',
            'role' => 'user',
        ])
        ->assertInvalid(['email']);
});

it('allows owner to delete pending invitation', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    $invitation = UserInvitation::factory()
        ->for($account)
        ->for($owner, 'invitedBy')
        ->create();

    actingAsAccount($owner, $account)
        ->from(route('users'))
        ->followingRedirects()
        ->delete(route('user-invitations.destroy', $invitation->uuid))
        ->assertSuccessful();

    expect(UserInvitation::query()->find($invitation->id))->toBeNull();
});

it('forbids non-owner from deleting invitation', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    $user = User::factory()->create();

    $account->users()->attach($owner, [
        'role' => UserAccountRole::Owner->value,
    ]);

    $invitation = UserInvitation::factory()
        ->for($account)
        ->for($owner, 'invitedBy')
        ->create();

    actingAsAccount($user, $account, UserAccountRole::User)
        ->delete(route('user-invitations.destroy', $invitation->uuid))
        ->assertForbidden();

    expect(UserInvitation::query()->find($invitation->id))->not->toBeNull();
});

it('returns not found when deleting non-existent invitation', function () {
    $nonExistentUuid = Str::uuid()->toString();

    actingAsAccount()
        ->delete(route('user-invitations.destroy', $nonExistentUuid))
        ->assertNotFound();
});

it('returns not found when deleting invitation from different account', function () {
    $otherAccount = createAccount();

    $invitation = UserInvitation::factory()
        ->for($otherAccount)
        ->for(User::factory()->create(), 'invitedBy')
        ->create();

    actingAsAccount()
        ->delete(route('user-invitations.destroy', $invitation->uuid))
        ->assertNotFound();

    expect(UserInvitation::query()->find($invitation->id))->not->toBeNull();
});

it('forbids deleting accepted invitation', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    $invitation = UserInvitation::factory()
        ->for($account)
        ->for($owner, 'invitedBy')
        ->accepted()
        ->create();

    actingAsAccount($owner, $account)
        ->delete(route('user-invitations.destroy', $invitation->uuid))
        ->assertBadRequest();

    expect(UserInvitation::query()->find($invitation->id))->not->toBeNull();
});
