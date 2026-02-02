<?php


namespace App\Http\Controllers\Settings;


use App\Facades\Accounts;
use App\Rules\TranslatableRule;
use App\Support\MailConfiguration;
use App\Support\Patch;
use App\Translation\TranslatableString;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use RuntimeException;
use StackTrace\Ui\SelectOption;

class MailController
{
    public function index()
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        $config = $account->getMailConfiguration();

        $mailer = new Fluent($config->mailer() ?: []);

        return Inertia::render('Settings/MailPage', [
            'font' => $config->font(),
            'footer' => $config->footer(),
            'alignment' => $config->alignment(),
            'sender' => $config->sender(),
            'mailer' => $driver = $mailer->string('driver')->value(),
            'smtpHost' => $driver === 'smtp' ? $mailer->string('host') : null,
            'smtpPort' => $driver === 'smtp' ? $mailer->string('port') : null,
            'smtpUsername' => $driver === 'smtp' ? $mailer->string('username') : null,
            'smtpPassword' => $driver === 'smtp' ? $mailer->string('password') : null,
            'sendgridApiKey' => $driver === 'sendgrid' ? $mailer->string('api_key') : null,
            'senderEmail' => $mailer->string('from'),
            'senderName' => $config->senderName(),
            'carbonCopy' => $config->carbonCopy(),
            'blindCarbonCopy' => $config->blindCarbonCopy(),
            'replyTo' => $config->replyTo(),

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
            'footer' => ['sometimes', TranslatableRule::make(['string', 'max:1000'])->nullable()],
            'alignment' => ['sometimes', 'required', 'string', Rule::in([
                MailConfiguration::ALIGN_LEFT, MailConfiguration::ALIGN_CENTER,
            ])],
            'sender_email' => ['required_if:sender,custom', 'string', 'max:180', 'email'],
            'sender_name' => ['sometimes', 'nullable', 'string', 'max:180'],
            'sender' => ['sometimes', 'required', 'string', Rule::in(['custom', 'system'])],
            'mailer' => ['required_if:sender,custom', 'string', 'max:30', Rule::in(['smtp', 'sendgrid'])],
            'smtp_host' => ['required_if:mailer,smtp', 'string', 'max:180', 'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i'],
            'smtp_port' => ['required_if:mailer,smtp', 'integer', 'between:1,65535'],
            'smtp_username' => ['required_if:mailer,smtp', 'string', 'max:200'],
            'smtp_password' => ['required_if:mailer,smtp', 'string', 'max:200'],
            'sendgrid_api_key' => ['required_if:mailer,sendgrid', 'string', 'max:300'],
            'carbon_copy' => ['sometimes', 'array', 'min:0', 'max:10'],
            'carbon_copy.*' => ['string', 'email', 'max:180'],
            'blind_carbon_copy' => ['sometimes', 'array', 'min:0', 'max:10'],
            'blind_carbon_copy.*' => ['string', 'email', 'max:180'],
            'reply_to' => ['sometimes', 'array', 'min:0', 'max:10'],
            'reply_to.*' => ['string', 'email', 'max:180'],
        ]);

        $patch = new Patch($input);

        $config = $account->getMailConfiguration();

        $patch->present('font', fn (string $font) => $config->setFont($font));
        $patch->present('footer', fn (?array $footer) => $config->setFooter(TranslatableString::fromArray($footer)));
        $patch->present('alignment', fn (string $alignment) => $config->setAlignment($alignment));
        $patch->present('sender_name', fn (?string $name) => $config->setSenderName($name));
        $patch->present('carbon_copy', fn (array $value) => $config->setCarbonCopy($value));
        $patch->present('blind_carbon_copy', fn (array $value) => $config->setBlindCarbonCopy($value));
        $patch->present('reply_to', fn (array $value) => $config->setReplyTo($value));

        if ($request->has('sender')) {
            $config->setSender($sender = $request->input('sender'));

            if ($sender === 'custom') {
                $mailer = $request->input('mailer');
                $senderEmail = $request->input('sender_email');

                if ($mailer == 'smtp') {
                    $config->setMailer([
                        'driver' => 'smtp',
                        'from' => $senderEmail,
                        'host' => $request->input('smtp_host'),
                        'port' => $request->input('smtp_port'),
                        'username' => $request->input('smtp_username'),
                        'password' => $request->input('smtp_password'),
                    ]);
                } else if ($mailer === 'sendgrid') {
                    $config->setMailer([
                        'driver' => 'sendgrid',
                        'from' => $senderEmail,
                        'api_key' => $request->input('sendgrid_api_key'),
                    ]);
                } else {
                    throw new RuntimeException("Invalid mailer");
                }
            } else {
                $config->setMailer(null);
            }
        }

        $account->setMailConfiguration($config);

        $account->save();

        return back();
    }
}
