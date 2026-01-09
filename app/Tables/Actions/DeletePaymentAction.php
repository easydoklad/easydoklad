<?php


namespace App\Tables\Actions;


use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use StackTrace\Ui\Selection;
use StackTrace\Ui\Table\Actions\Action;

class DeletePaymentAction extends Action
{
    protected ?string $title = 'Odstrániť';
    protected ?string $label = 'Odstrániť';
    protected ?string $description = 'Naozaj chcete odstrániť túto platbu/platby?';
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
        DB::transaction(fn () => Payment::query()->whereIn('id', $selection->all())->eachById(function (Payment $payment) {
            if (Gate::allows('delete', $payment)) {
                $payment->delete();
            }
        }));
    }
}
