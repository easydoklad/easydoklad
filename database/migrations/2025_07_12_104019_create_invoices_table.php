<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->boolean('draft');
            $table->boolean('sent');
            $table->boolean('paid');
            $table->boolean('locked');
            $table->foreignId('account_id')->constrained('accounts');
            $table->foreignId('supplier_id')->constrained('companies');
            $table->foreignId('customer_id')->constrained('companies');
            $table->string('public_invoice_number')->nullable();
            $table->integer('invoice_number')->nullable();
            $table->foreignId('number_sequence_id')->nullable()->constrained('number_sequences');
            $table->string('payment_method');
            $table->string('variable_symbol')->nullable();
            $table->string('specific_symbol')->nullable();
            $table->string('constant_symbol')->nullable();
            $table->string('currency');
            $table->unsignedBigInteger('total_vat_inclusive')->nullable();
            $table->unsignedBigInteger('total_vat_exclusive')->nullable();
            $table->unsignedBigInteger('remaining_to_pay')->nullable();
            $table->unsignedBigInteger('total_to_pay')->nullable();
            $table->foreignId('signature_id')->nullable()->constrained('uploads');
            $table->foreignId('logo_id')->nullable()->constrained('uploads');
            $table->string('issued_by')->nullable();
            $table->string('issued_by_email')->nullable();
            $table->string('issued_by_phone_number')->nullable();
            $table->string('issued_by_website')->nullable();
            $table->boolean('vat_enabled');
            $table->foreignId('template_id')->constrained('document_templates');
            $table->text('footer_note')->nullable();
            $table->boolean('vat_reverse_charge');
            $table->boolean('show_pay_by_square');
            $table->date('issued_at')->nullable();
            $table->date('supplied_at')->nullable();
            $table->date('payment_due_to')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
