<?php

namespace App\Tables\Actions;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use StackTrace\Ui\Selection;
use StackTrace\Ui\Table\Actions\Action;

class DuplicateInvoiceAction extends Action
{
    protected ?string $title = 'Duplikovať';

    protected ?string $label = 'Duplikovať';

    protected ?string $description = 'Chcete vytvoriť kópiu tejto faktúry?';

    protected ?string $cancelLabel = 'Zrušiť';

    protected ?string $confirmLabel = 'Vytvoriť kópiu';

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function handle(Selection $selection): void
    {
        DB::transaction(fn () => Invoice::query()->whereIn('id', $selection->all())->eachById(function (Invoice $invoice) {
            if (Gate::allows('view', $invoice) && ! $invoice->draft) {
                $invoice->duplicate();
            }
        }));
    }
}
