<?php

namespace App\Tables\Actions;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use StackTrace\Ui\Selection;
use StackTrace\Ui\Table\Actions\Action;

class DiscardInvoiceDraftAction extends Action
{
    protected ?string $title = 'Zahodiť koncept';

    protected ?string $label = 'Zahodiť koncept';

    protected ?string $description = 'Naozaj chcete odstrániť túto faktúru/faktúry?';

    protected ?string $cancelLabel = 'Ponechať';

    protected ?string $confirmLabel = 'Odstrániť';

    protected bool $destructive = true;

    protected bool $bulk = true;

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function handle(Selection $selection): void
    {
        DB::transaction(fn () => Invoice::query()->whereIn('id', $selection->all())->eachById(function (Invoice $invoice) {
            if (Gate::allows('delete', $invoice) && $invoice->draft) {
                $invoice->delete();
            }
        }));
    }
}
