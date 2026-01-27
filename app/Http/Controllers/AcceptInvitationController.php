<?php


namespace App\Http\Controllers;


use App\Facades\Accounts;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use StackTrace\Ui\Facades\Toast;

class AcceptInvitationController
{
    public function create(string $invitation)
    {
        /** @var UserInvitation $invitation */
        $invitation = UserInvitation::query()->firstWhere('token', $invitation);

        if (!$invitation || $invitation->isExpired() || $invitation->isAccepted()) {
            return Inertia::render('Account/AcceptInvitationPage', [
                'status' => match (true) {
                    $invitation && $invitation->isAccepted() => 'accepted',
                    $invitation && $invitation->isExpired() => 'expired',
                    default => 'invalid',
                },
            ]);
        }

        if (($user = Auth::user()) && $user->accounts->contains($invitation->account)) {
            return Inertia::render('Account/AcceptInvitationPage', [
                'status' => 'duplicate',
            ]);
        }

        return Inertia::render('Account/AcceptInvitationPage', [
            'status' => 'pending',
            'invitation' => [
                'token' => $invitation->token,
                'account' => $invitation->account->company->business_name,
                'guest' => Auth::guest(),
            ],
        ]);
    }

    public function store(string $invitation)
    {
        /** @var UserInvitation $invitation */
        $invitation = UserInvitation::query()->firstWhere('token', $invitation);

        abort_if(is_null($invitation), 404);

        $lock = $invitation->lock();

        $user = Auth::user();

        if ($user->accounts->contains($invitation->account)) {
            Toast::destructive('Už máte prístup k tejto firme.');
            return back();
        }

        try {
            $lock->block(5);

            $invitation->refresh();

            if ($invitation->isAccepted()) {
                Toast::destructive('Táto pozvánka už bola akceptovaná.');
                return back();
            }

            if ($invitation->isExpired()) {
                Toast::destructive('Platnosť tejto pozvánky vypršala.');
                return back();
            }

            DB::transaction(fn () => $invitation->accept($user));
        } finally {
            $lock->release();
        }

        $user->refresh();

        Accounts::switch($invitation->account);

        Toast::make('Pozvánka bola akceptovaná.', variant: 'positive');

        return to_route('home');
    }
}
