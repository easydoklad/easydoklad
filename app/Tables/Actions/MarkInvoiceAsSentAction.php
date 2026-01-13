<?php

namespace App\Tables\Actions;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use StackTrace\Ui\Selection;
use StackTrace\Ui\Table\Actions\Action;

class MarkInvoiceAsSentAction extends Action
{
    protected ?string $title = 'Označiť ako odoslané';

    protected ?string $label = 'Označiť ako odoslané';

    protected ?string $description = 'Naozaj chcete označiť túto faktúru/faktúry ako odoslanú/odoslané?';

    protected ?string $cancelLabel = 'Zrušiť';

    protected ?string $confirmLabel = 'Označiť';

    protected bool $bulk = true;

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function handle(Selection $selection): void
    {
        DB::transaction(fn () => Invoice::query()->whereIn('id', $selection->all())->eachById(function (Invoice $invoice) {
            if (Gate::allows('update', $invoice) && ! $invoice->draft && ! $invoice->sent) {
                $invoice->sent = true;
                $invoice->save();
            }
        }));
    }
}
