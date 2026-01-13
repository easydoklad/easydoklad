<?php

namespace App\Http\Controllers\Settings;

use App\Facades\Accounts;
use App\Models\TemporaryUpload;
use App\Models\Upload;
use App\Rules\TemporaryUploadRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChangeInvoiceLogoController
{
    public function __invoke(Request $request)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        $request->validate([
            'file' => [TemporaryUploadRule::scope('InvoiceLogo')],
            'remove' => ['boolean'],
        ]);

        $file = $request->input('file');
        $remove = $request->boolean('remove');

        if ($remove && ($logo = $account->invoiceLogo)) {
            $account->invoiceLogo()->dissociate()->save();
            $logo->delete();
        } elseif ($file) {
            $temporaryUpload = TemporaryUpload::findOrFailByUUID($file);
            $upload = Upload::storePublicly($temporaryUpload);
            $account->invoiceLogo()->associate($upload)->save();
            $temporaryUpload->delete();
        }

        return back();
    }
}
