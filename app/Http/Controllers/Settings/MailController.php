<?php


namespace App\Http\Controllers\Settings;


use App\Facades\Accounts;
use App\Support\MailConfiguration;
use App\Support\Patch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use StackTrace\Ui\SelectOption;

class MailController
{
    public function index()
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        $config = $account->getMailConfiguration();

        return Inertia::render('Settings/MailPage', [
            'font' => $config->font(),
            'footer' => $config->footer(),
            'alignment' => $config->alignment(),

            'fonts' => [
                new SelectOption('Arial', MailConfiguration::FONT_ARIAL),
                new SelectOption('Helvetica', MailConfiguration::FONT_HELVETICA),
                new SelectOption('Georgia', MailConfiguration::FONT_GEORGIA),
                new SelectOption('Times New Roman', MailConfiguration::FONT_TIMES_NEW_ROMAN),
            ],
            // TODO: Add replacements
            'replacements' => [
                [
                    'name' => 'Údaje o firme (vy)',
                    'replacements' => [
                        'dodavatel.nazov' => 'Názov dodávateľa',
                        'dodavatel.adresa' => 'Celá adresa dodávateľa',
                        'dodavatel.identifikatory' => 'IČO, DIČ a IČDPH dodávateľa',
                    ]
                ]
            ],
        ]);
    }

    public function update(Request $request)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        $input = $request->validate([
            'font' => ['sometimes', 'required', 'string', Rule::in([
                MailConfiguration::FONT_ARIAL, MailConfiguration::FONT_HELVETICA,
                MailConfiguration::FONT_TIMES_NEW_ROMAN, MailConfiguration::FONT_GEORGIA,
            ])],
            'footer' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'alignment' => ['sometimes', 'required', 'string', Rule::in([
                MailConfiguration::ALIGN_LEFT, MailConfiguration::ALIGN_CENTER,
            ])],
        ]);

        $patch = new Patch($input);

        $config = $account->getMailConfiguration();

        $patch->present('font', fn (string $font) => $config->setFont($font));
        $patch->present('footer', fn (?string $footer) => $config->setFooter($footer));
        $patch->present('alignment', fn (string $alignment) => $config->setAlignment($alignment));

        $account->setMailConfiguration($config);

        $account->save();

        return back();
    }
}
