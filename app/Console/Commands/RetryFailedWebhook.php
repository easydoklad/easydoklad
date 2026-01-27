<?php

namespace App\Console\Commands;

use App\Jobs\SendDispatchedWebhook;
use App\Models\DispatchedWebhook;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class RetryFailedWebhook extends Command
{
    protected $signature = 'webhook:retry {id?} {--all} {--hours=4}';

    protected $description = 'Retry failed webhook deliveries';

    public function handle(): int
    {
        if ($id = $this->argument('id')) {
            $dispatch = DispatchedWebhook::findOrFailByUUID($id);

            if ($dispatch->delivered()) {
                throw new InvalidArgumentException('The webhook has already been delivered.');
            }

            dispatch(new SendDispatchedWebhook($dispatch));

            $this->info("Webhook [{$dispatch->uuid}] retried");

            return self::SUCCESS;
        }

        $all = $this->option('all');
        $hours = (int) $this->option('hours');

        DispatchedWebhook::query()
            ->whereNull('delivered_at')
            ->unless($all, fn (Builder $builder) => $builder
                ->whereNotNull('last_attempt_at')
                ->where('attempts', '>', 0)
                ->where('last_attempt_at', '<', now()->subHours($hours))
            )
            ->when($all, fn (Builder $builder) => $builder
                ->where(fn (Builder $builder) => $builder
                    ->whereNull('last_attempt_at')->orWhere('last_attempt_at', '<', now()->subHours($hours))
                )
            )
            ->eachById(function (DispatchedWebhook $dispatch) {
                dispatch(new SendDispatchedWebhook($dispatch));

                $this->info("Webhook [{$dispatch->uuid}] retried");
            });

        return self::SUCCESS;
    }
}
