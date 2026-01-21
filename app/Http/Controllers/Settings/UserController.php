<?php


namespace App\Http\Controllers\Settings;


use App\Enums\UserAccountRole;
use App\Facades\Accounts;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use StackTrace\Ui\SelectOption;

class UserController
{
    public function index()
    {
        $account = Accounts::current();

        $currentUser = $account->getCurrentUser();

        $canManageUsers = Gate::allows('update', $account) && $currentUser?->getRole() === UserAccountRole::Owner;

        return Inertia::render('Settings/Users', [
            'users' => $account->users->sortBy('name')->values()->map(fn (User $user) => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRole()->asString(),
                'can' => [
                    'update' => $canManageUsers && !$user->is($currentUser),
                    'delete' => $canManageUsers && !$user->is($currentUser)
                ],
            ]),
            'invitations' => $account->userInvitations->map(fn (UserInvitation $invitation) => [
                'id' => $invitation->uuid,
                'email' => $invitation->email,
                'role' => $invitation->role->label(),
                'expired' => $invitation->isExpired(),
                'can' => [
                    'revoke' => $canManageUsers,
                    'resend' => $canManageUsers,
                ],
            ]),
            'can' => [
                'invite' => $canManageUsers,
            ],
            'roles' => collect([
                new SelectOption('Majiteľ', 'owner'),
                new SelectOption('Používateľ', 'user'),
            ]),
            'expirationHours' => config('app.invitation_expiration_hours'),
        ]);
    }
}
