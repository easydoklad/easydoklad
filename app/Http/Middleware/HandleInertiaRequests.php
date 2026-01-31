<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use App\View\Models\UserViewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => function () use ($request) {
                    $user = $request->user();

                    return $user ? new UserViewModel($user) : null;
                },
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',

            'locale' => fn () => App::getLocale(),
            'fallbackLocale' => fn () => App::getFallbackLocale(),
            'locales' => fn () => Locale::all()->map(fn (Locale $locale) => [
                'code' => $locale->code,
                'name' => $locale->name,
                // TODO: môže byť na urovi učtu nastavne...
                'required' => $locale->code === 'sk',
            ]),
        ];
    }
}
