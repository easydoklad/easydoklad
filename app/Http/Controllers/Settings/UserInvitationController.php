<?php


namespace App\Http\Controllers\Settings;


use App\Enums\UserAccountRole;
use App\Facades\Accounts;
use App\Models\User;
use App\Models\UserInvitation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use StackTrace\Ui\Facades\Toast;

class UserInvitationController
{
    public function store(Request $request)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);
        abort_unless($account->getCurrentUser()?->getRole() === UserAccountRole::Owner, 403);

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:180', function (string $attribute, string $value, Closure $fail) use ($account) {
                if ($account->users->map(fn (User $user) => Str::lower($user->email))->contains(Str::lower($value))) {
                    $fail('Tento používateľ už má prístup k vašej firme.');
                }

                if ($account->userInvitations()->whereNull('accepted_at')->get()->map(fn (UserInvitation $invitation) => Str::lower($invitation->email))->contains(Str::lower($value))) {
                    $fail('Tento používateľ už bol pozvaný.');
                }
            }],
            'role' => ['required', 'string', 'max:10', Rule::in(['owner', 'user'])],
        ]);

        /** @var UserInvitation $invitation */
        $invitation = $account->userInvitations()->make([
            'token' => Str::lower(Str::random(30)),
            'email' => $request->input('email'),
            'role' => UserAccountRole::fromString($request->input('role')),
            'expires_at' => now()->addHours(config('app.invitation_expiration_hours')),
        ]);

        $invitation->invitedBy()->associate(Auth::user());
        $invitation->save();

        $invitation->send();

        Toast::make('Používateľ bol pozvaný.', $invitation->email);

        return back();
    }

    public function destroy(UserInvitation $invitation)
    {
        $account = Accounts::current();
        Gate::allows('update', $account);
        abort_unless($account->getCurrentUser()?->getRole() === UserAccountRole::Owner, 403);

        DB::transaction(fn () => $invitation->delete());

        return back();
    }
}
