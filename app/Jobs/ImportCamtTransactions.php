<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\BankTransaction;
use App\Services\BankingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImportCamtTransactions implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Account $account,
        public string $filePath,
    ) { }

    public function handle(BankingService $bankingService): void
    {
        $contents = Storage::get($this->filePath);

        DB::transaction(function () use ($contents, $bankingService) {
            $bankingService
                ->recordTransactionsFromCamt($this->account, $contents)
                ->each(function (BankTransaction $transaction) use ($bankingService) {
                    $bankingService->pairTransaction($transaction);
                });
        });


        Storage::deleteDirectory(File::dirname($this->filePath));
    }
}
