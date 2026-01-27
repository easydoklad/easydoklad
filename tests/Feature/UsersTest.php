<?php

use App\Enums\UserAccountRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

it('shows users index for owner with management permissions', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    $otherUser = User::factory()->create();

    $account->users()->attach($otherUser, [
        'role' => UserAccountRole::User->value,
    ]);

    actingAsAccount($owner, $account, UserAccountRole::Owner)
        ->get(route('users'))
        ->assertSuccessful()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Settings/Users')
                ->has('users', 2)
                ->where('can.invite', true)
                ->where('users', fn (Collection $users) => $users->contains('id', $owner->uuid))
                ->where('users', fn (Collection $users) => $users->contains('id', $otherUser->uuid)),
        );
});

it('shows users index for regular user without management permissions', function () {
    actingAsAccount(role: UserAccountRole::User)
        ->get(route('users'))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Settings/Users')
            ->has('users')
            ->where('can.invite', false)
        );
});

it('allows owner to update user role', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    $targetUser = User::factory()->create();

    $account->users()->attach($targetUser, [
        'role' => UserAccountRole::User->value,
    ]);

    actingAsAccount($owner, $account, UserAccountRole::Owner)
        ->from(route('users'))
        ->followingRedirects()
        ->patch(route('users.update', $targetUser->uuid), [
            'role' => 'owner',
        ])
        ->assertSuccessful();

    $pivotRole = $account
        ->fresh()
        ->users
        ->firstWhere('id', $targetUser->id)
        ->pivot
        ->role;

    expect($pivotRole)->toBe(UserAccountRole::Owner->value);
});

it('forbids non-owner from updating user role', function () {
    $user = User::factory()->create();
    $account = createAccount();

    $otherUser = User::factory()->create();

    $account->users()->attach($otherUser, [
        'role' => UserAccountRole::User->value,
    ]);

    actingAsAccount($user, $account, UserAccountRole::User)
        ->patch(route('users.update', $otherUser->uuid), [
            'role' => 'owner',
        ])
        ->assertForbidden();
});

it('returns not found when updating user outside of account', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    $unrelatedUser = User::factory()->create();

    actingAsAccount($owner, $account, UserAccountRole::Owner)
        ->patch(route('users.update', $unrelatedUser->uuid), [
            'role' => 'owner',
        ])
        ->assertNotFound();
});

it('allows owner to remove user from account', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    $targetUser = User::factory()->create();

    $account->users()->attach($targetUser, [
        'role' => UserAccountRole::User->value,
    ]);

    actingAsAccount($owner, $account, UserAccountRole::Owner)
        ->from(route('users'))
        ->followingRedirects()
        ->delete(route('users.destroy', $targetUser->uuid))
        ->assertSuccessful();

    expect($account->fresh())
        ->users
        ->contains($targetUser)
        ->toBeFalse();
});

it('forbids non-owner from removing user from account', function () {
    $user = User::factory()->create();
    $account = createAccount();

    $targetUser = User::factory()->create();

    $account->users()->attach($targetUser, [
        'role' => UserAccountRole::User->value,
    ]);

    actingAsAccount($user, $account, UserAccountRole::User)
        ->delete(route('users.destroy', $targetUser->uuid))
        ->assertForbidden();
});

it('returns not found when removing user outside of account', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    $unrelatedUser = User::factory()->create();

    actingAsAccount($owner, $account, UserAccountRole::Owner)
        ->delete(route('users.destroy', $unrelatedUser->uuid))
        ->assertNotFound();
});

it('forbids owner from removing themself from account', function () {
    $owner = User::factory()->create();
    $account = createAccount();

    actingAsAccount($owner, $account, UserAccountRole::Owner)
        ->delete(route('users.destroy', $owner->uuid))
        ->assertForbidden();

    expect($account->fresh())
        ->users
        ->contains($owner)
        ->toBeTrue();
});
