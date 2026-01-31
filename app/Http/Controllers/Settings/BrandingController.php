<?php


namespace App\Http\Controllers\Settings;


use App\Facades\Accounts;
use App\Models\Upload;
use App\Rules\TemporaryUploadRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BrandingController
{
    public function index()
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        return Inertia::render('Settings/BrandingPage', [
            'squareLogoUrl' => $account->squareLogo?->url(),
            'wideLogoUrl' => $account->wideLogo?->url(),
        ]);
    }

    public function update(Request $request)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        $request->validate([
            'square_logo' => [TemporaryUploadRule::scope('BrandingSquareLogo')],
            'remove_square_logo' => ['boolean'],
            'wide_logo' => [TemporaryUploadRule::scope('BrandingWideLogo')],
            'remove_wide_logo' => ['boolean'],
        ]);

        DB::transaction(function () use ($request, $account) {
            Upload::syncRelation(
                model: $account,
                relation: 'squareLogo',
                remove: $request->boolean('remove_square_logo'),
                file: $request->input('square_logo'),
            );

            Upload::syncRelation(
                model: $account,
                relation: 'wideLogo',
                remove: $request->boolean('remove_wide_logo'),
                file: $request->input('wide_logo'),
            );
        });

        return back();
    }
}
