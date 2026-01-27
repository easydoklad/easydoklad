<?php


namespace App\Http\Controllers\Settings;


use App\Enums\UserAccountRole;
use App\Facades\Accounts;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
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
                'id' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRole()->asString(),
                'can' => [
                    'update' => $canManageUsers && !$user->is($currentUser),
                    'delete' => $canManageUsers && !$user->is($currentUser)
                ],
            ]),
            'invitations' => $account
                ->userInvitations()
                ->whereNull('accepted_at')
                ->get()
                ->map(fn (UserInvitation $invitation) => [
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

    public function update(Request $request, User $user)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);
        abort_unless($account->getCurrentUser()?->getRole() === UserAccountRole::Owner, 403);

        $request->validate([
            'role' => ['required', 'string', 'max:10', Rule::in(['owner', 'user'])],
        ]);

        $role = UserAccountRole::fromString($request->input('role'));

        if ($account->users->contains($user)) {
            $account->users()->updateExistingPivot($user, [
                'role' => $role,
            ]);
        } else {
            abort(404);
        }

        return back();
    }

    public function destroy(User $user)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);
        abort_unless($account->getCurrentUser()?->getRole() === UserAccountRole::Owner, 403);

        if ($account->users->contains($user)) {
            $account->users()->detach($user);
        } else {
            abort(404);
        }

        return back();
    }
}
