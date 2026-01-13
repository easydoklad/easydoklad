<?php

namespace App\Http\Controllers\Banking;

use App\Facades\Accounts;
use App\Jobs\ImportCamtTransactions;
use App\Models\TemporaryUpload;
use App\Rules\TemporaryUploadRule;
use Illuminate\Http\Request;

class CamtImportController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'file' => [TemporaryUploadRule::scope('BankTransactionsCamt')],
        ]);

        $upload = TemporaryUpload::findOrFailByUUID($request->input('file'));

        dispatch(new ImportCamtTransactions(Accounts::current(), $upload->copyToWorkspace()));

        $upload->delete();

        return back();
    }
}
