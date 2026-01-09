<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transaction_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->foreignId('account_id')->constrained('accounts');
            $table->string('type');
            $table->string('name');
            $table->string('iban');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transaction_accounts');
    }
};
