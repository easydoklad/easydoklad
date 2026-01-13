<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Country;
use App\Facades\Accounts;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AccountController
{
    public function edit()
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        return Inertia::render('Settings/Account', [
            'id' => $account->id,
            'countries' => Country::options(),
            'businessName' => $account->company->business_name,
            'businessId' => $account->company->business_id,
            'vatId' => $account->company->vat_id,
            'euVatId' => $account->company->eu_vat_id,
            'website' => $account->company->website,
            'email' => $account->company->email,
            'phoneNumber' => $account->company->phone_number,
            'additionalInfo' => $account->company->additional_info,
            'addressLineOne' => $account->company->address?->line_one,
            'addressLineTwo' => $account->company->address?->line_two,
            'addressLineThree' => $account->company->address?->line_three,
            'addressCity' => $account->company->address?->city,
            'addressPostalCode' => $account->company->address?->postal_code,
            'addressCountry' => $account->company->address?->country?->value,
            'bankName' => $account->company->bank_name,
            'bankBic' => $account->company->bank_bic,
            'bankAddress' => $account->company->bank_address,
            'bankAccountIban' => $account->company->bank_account_iban,
            'bankAccountNumber' => $account->company->bank_account_number,
            'vatEnabled' => $account->vat_enabled,
        ]);
    }

    public function update(Request $request)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        $request->validate([
            'business_name' => ['required', 'string', 'max:191'],
            'business_id' => ['nullable', 'string', 'max:191'],
            'vat_id' => ['nullable', 'string', 'max:191'],
            'eu_vat_id' => ['nullable', 'string', 'max:191'],
            'address_line_one' => ['nullable', 'string', 'max:191'],
            'address_line_two' => ['nullable', 'string', 'max:191'],
            'address_line_three' => ['nullable', 'string', 'max:191'],
            'address_city' => ['nullable', 'string', 'max:191'],
            'address_postal_code' => ['nullable', 'string', 'max:191'],
            'address_country' => ['nullable', 'string', Rule::enum(Country::class)],
            'website' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'string', 'email', 'max:191'],
            'phone_number' => ['nullable', 'string', 'max:191'],
            'additional_info' => ['nullable', 'string', 'max:500'],
            'vat_enabled' => ['boolean'],
        ]);

        DB::transaction(function () use ($account, $request) {
            $company = $account->company;

            $address = $company->address ?: new Address;
            $address->fill([
                'line_one' => $request->input('address_line_one'),
                'line_two' => $request->input('address_line_two'),
                'line_three' => $request->input('address_line_three'),
                'city' => $request->input('address_city'),
                'postal_code' => $request->input('address_postal_code'),
                'country' => $request->enum('address_country', Country::class),
            ]);
            $address->save();

            $company->address()->associate($address);
            $company->fill([
                'business_name' => $request->input('business_name'),
                'business_id' => $request->input('business_id'),
                'vat_id' => $request->input('vat_id'),
                'eu_vat_id' => $request->input('eu_vat_id'),
                'website' => $request->input('website'),
                'email' => $request->input('email'),
                'phone_number' => $request->input('phone_number'),
                'additional_info' => $request->input('additional_info'),
            ]);
            $company->save();

            $account->update([
                'vat_enabled' => $request->boolean('vat_enabled'),
            ]);
        });

        return back();
    }
}
