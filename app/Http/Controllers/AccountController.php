<?php


namespace App\Http\Controllers;


use App\Enums\Country;
use App\Enums\UserAccountRole;
use App\Facades\Accounts;
use App\Models\Account;
use App\Models\Address;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use StackTrace\Ui\SelectOption;

class AccountController
{
    public function create()
    {
        return Inertia::render('Account/CreateAccountPage', [
            'countries' => collect([
                SelectOption::fromEnum(Country::Slovakia),
            ]),
            'defaultCountry' => Country::Slovakia->value,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:180'],
            'business_id' => ['nullable', 'string', 'max:180'],
            'vat_id' => ['nullable', 'string', 'max:180'],
            'eu_vat_id' => ['nullable', 'string', 'max:180'],
            'vat_enabled' => ['boolean'],
            'address_line_one' => ['required', 'string', 'max:180'],
            'address_line_two' => ['nullable', 'string', 'max:180'],
            'address_city' => ['required', 'string', 'max:180'],
            'address_postal_code' => ['required', 'string', 'max:180'],
            'address_country' => ['required', 'string', Rule::enum(Country::class)->only(Country::Slovakia)],
        ]);

        $user = Auth::user();

        $account = DB::transaction(function () use ($request, $user) {
            $country = $request->enum('address_country', Country::class);

            $address = Address::create([
                'line_one' => $request->input('address_line_one'),
                'line_two' => $request->input('address_line_two'),
                'city' => $request->input('address_city'),
                'postal_code' => $request->input('address_postal_code'),
                'country' => $country,
            ]);

            $company = new Company([
                'business_name' => $request->input('business_name'),
                'business_id' => $request->input('business_id'),
                'vat_id' => $request->input('vat_id'),
                'eu_vat_id' => $request->input('eu_vat_id'),
            ]);
            $company->address()->associate($address);
            $company->save();

            $account = Account::makeWithDefaults();
            if ($request->boolean('vat_enabled')) {
                $account->vat_enabled = true;
                $account->default_vat_rate = Arr::get(config('app.default_vat_rates', []), $country->value);
            }

            $account->company()->associate($company);

            $account->save();

            $user->accounts()->attach($account, ['role' => UserAccountRole::Owner]);

            return $account;
        });

        Accounts::switch($account);

        return to_route('dashboard');
    }
}
