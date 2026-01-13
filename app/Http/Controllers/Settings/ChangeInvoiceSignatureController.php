<?php

namespace App\Http\Controllers\Settings;

use App\Facades\Accounts;
use App\Models\TemporaryUpload;
use App\Models\Upload;
use App\Rules\TemporaryUploadRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChangeInvoiceSignatureController
{
    public function __invoke(Request $request)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        $request->validate([
            'file' => [TemporaryUploadRule::scope('InvoiceSignature')],
            'remove' => ['boolean'],
        ]);

        $file = $request->input('file');
        $remove = $request->boolean('remove');

        if ($remove && ($logo = $account->invoiceSignature)) {
            $account->invoiceSignature()->dissociate()->save();
            $logo->delete();
        } elseif ($file) {
            $temporaryUpload = TemporaryUpload::findOrFailByUUID($file);
            $upload = Upload::storePublicly($temporaryUpload);
            $account->invoiceSignature()->associate($upload)->save();
            $temporaryUpload->delete();
        }

        return back();
    }
}
