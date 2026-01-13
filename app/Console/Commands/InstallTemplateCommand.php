<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\DocumentTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallTemplateCommand extends Command
{
    protected $signature = 'template:install
                            { path : path to the template directory or archive }
                            { --account=* : account for which the template should be installed }';

    protected $description = 'Install document template';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! File::exists($path)) {
            $this->error('Provided path does not exist');

            return self::FAILURE;
        }

        $accounts = [];

        $accountIds = $this->option('account');
        if (! empty($accountIds)) {
            $existingAccounts = Account::query()->whereIn('id', $accountIds)->get()->keyBy('id');

            foreach ($accountIds as $id) {
                if ($account = $existingAccounts->get((int) $id)) {
                    $accounts[] = $account;
                } else {
                    $this->error("The account with ID [$id] does not exist");

                    return self::FAILURE;
                }
            }
        }

        $printResult = function (DocumentTemplate $template) {
            $message = "✔ {$template->package} ".($template->wasRecentlyCreated ? 'installed' : 'updated');

            if ($template->account) {
                $message .= " for account {$template->account->id}";
            }

            $this->info($message);
        };

        if (File::isDirectory($path)) {
            if (empty($accounts)) {
                $template = DocumentTemplate::installFromDirectory($path);

                $printResult($template);
            } else {
                foreach ($accounts as $account) {
                    $template = DocumentTemplate::installFromDirectory($path, $account);

                    $printResult($template);
                }
            }
        } else {
            if (empty($accounts)) {
                $template = DocumentTemplate::installFromArchive($path);

                $printResult($template);
            } else {
                foreach ($accounts as $account) {
                    $template = DocumentTemplate::installFromArchive($path, $account);

                    $printResult($template);
                }
            }
        }

        return self::SUCCESS;
    }
}
