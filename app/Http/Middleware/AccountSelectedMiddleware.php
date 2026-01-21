<?php

namespace App\Http\Middleware;

use App\Facades\Accounts;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class AccountSelectedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Accounts::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if (! $user) {
            return to_route('login');
        }

        if ($user->accounts->isEmpty()) {
            return to_route('accounts.create');
        }

        throw new RuntimeException("Invalid state. No account selected.");
    }
}
