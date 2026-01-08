<?php

namespace App\Providers;

use App\Banking\BankTransactionMailHandler;
use App\Banking\MailParsers\TatraBankaBMailParser;
use App\Enums\BankTransactionAccountType;
use App\Facades\Accounts;
use App\Mail\Mailbox;
use App\Models\User;
use App\Services\AccountService;
use BeyondCode\Mailbox\Facades\Mailbox as MailboxRouter;
use BeyondCode\Mailbox\InboundEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(AccountService::class);
        $this->app->alias(AccountService::class, 'accounts');

        $this->app->singleton(Mailbox::class);

        $this->app->singleton(BankTransactionMailHandler::class);
        $this->app->extend(BankTransactionMailHandler::class, function (BankTransactionMailHandler $handler) {
            return $handler->registerParser(BankTransactionAccountType::TatraBankBMail, TatraBankaBMailParser::class);
        });

        Relation::enforceMorphMap([
            'invoice' => \App\Models\Invoice::class,
        ]);
    }

    public function boot(): void
    {
        MailboxRouter::catchAll(Mailbox::class);

        RateLimiter::for('mail', function (Request $request) {
            return Limit::perMinute(20)->by($request->user() ? Accounts::current()->id : $request->ip());
        });

        Feature::define('expenses', fn (User $user) => match (true) {
            // in_array($user->email, ['peter@peterstovka.com', 'ps@stacktrace.sk']) => true,
            default => false,
        });
    }
}
