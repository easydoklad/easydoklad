<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use StackTrace\Ui\Facades\Toast;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'invitation' => ['sometimes', 'required', 'string', Rule::exists(UserInvitation::class, 'token')],
        ]);

        $createUser = function () use ($request) {
            return User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        };

        if ($request->has('invitation')) {
            $invitation = UserInvitation::query()->firstWhere('token', $request->input('invitation'));

            $lock = $invitation->lock();

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

                $user = DB::transaction(function () use ($invitation, $createUser) {
                    $user = $createUser();

                    $invitation->accept($user);

                    return $user;
                });
            } finally {
                $lock->release();
            }
        } else {
            $user = $createUser();
        }

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
