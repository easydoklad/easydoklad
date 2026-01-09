<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->foreignId('account_id')->constrained('accounts');
            $table->foreignId('bank_transaction_account_id')->constrained('bank_transaction_accounts');
            $table->string('type');
            $table->string('sent_from_iban');
            $table->string('sent_from_name')->nullable();
            $table->string('received_to_iban');
            $table->bigInteger('amount');
            $table->string('currency');
            $table->date('transaction_date');
            $table->string('variable_symbol')->nullable();
            $table->string('specific_symbol')->nullable();
            $table->string('constant_symbol')->nullable();
            $table->string('description')->nullable();
            $table->string('reference')->nullable();
            $table->string('hash')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
