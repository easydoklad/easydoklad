<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Enums\PaymentMethod;
use App\Enums\UserAccountRole;
use App\Models\Account;
use App\Models\Address;
use App\Models\Company;
use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Peter Stovka',
            'email' => 'ps@stacktrace.sk',
        ]);

        $template = DocumentTemplate::query()
            ->where('document_type', DocumentType::Invoice)
            ->where('is_default', true)
            ->first();

        if (! $template) {
            throw new RuntimeException('The default document templates were not installed prior to running this seeder');
        }

        $account = Account::factory()
            ->for(
                Company::factory()
                    ->for(
                        Address::factory()->create([
                            'line_one' => 'Rastislavova 2151',
                            'postal_code' => '093 02',
                            'city' => 'Hencovce',
                            'country' => 'sk',
                        ])
                    )
                    ->create([
                        'business_name' => 'StackTrace s.r.o.',
                        'business_id' => '53736630',
                        'vat_id' => '2121479052',
                        'eu_vat_id' => 'SK2121479052',
                        'email' => 'info@stacktrace.sk',
                        'website' => 'https://www.stacktrace.sk',
                        'phone_number' => '+421 950 498 911',
                        'bank_name' => 'Tatra Banka a.s.',
                        'bank_address' => 'Hodžovo námestie 3, 811 06 Bratislava 1',
                        'bank_bic' => 'TATRSKBX',
                        'bank_account_iban' => 'SK88 1100 0000 0029 4510 2347',
                    ])
            )
            ->create([
                'vat_enabled' => true,
                'default_vat_rate' => 23,
                'invoice_payment_method' => PaymentMethod::BankTransfer,
                'invoice_footer_note' => 'Spoločnosť je zapísaná v obchodnom registri Okresného súdu Prešov, oddiel: Sro, vložka č. 42064/P',
                'invoice_template_id' => $template->id,
            ]);

        $user->accounts()->attach($account, ['role' => UserAccountRole::Owner]);

        $account = Account::factory()
            ->for(
                Company::factory()
                    ->for(
                        Address::factory()->create([
                            'line_one' => 'Rastislavova 2151',
                            'postal_code' => '093 02',
                            'city' => 'Hencovce',
                            'country' => 'sk',
                        ])
                    )
                    ->create([
                        'business_name' => 'Peter Štovka',
                        'business_id' => '50898493',
                        'vat_id' => '1123299826',
                        'eu_vat_id' => null,
                        'email' => 'peter@peterstovka.com',
                        'website' => 'https://www.peterstovka.com',
                        'phone_number' => '+421 950 498 911',
                        'bank_name' => 'Tatra Banka a.s.',
                        'bank_address' => 'Hodžovo námestie 3, 811 06 Bratislava 1',
                        'bank_bic' => 'TATRSKBX',
                        'bank_account_iban' => 'SK86 1100 0000 0029 4925 2916',
                    ])
            )
            ->create([
                'vat_enabled' => false,
                'invoice_payment_method' => PaymentMethod::BankTransfer,
                'invoice_footer_note' => 'Zapísaný v ŽR OÚ Vranov nad Topľou č. 790-16724',
                'invoice_template_id' => $template->id,
            ]);

        $user->accounts()->attach($account, ['role' => UserAccountRole::Owner]);
    }
}
