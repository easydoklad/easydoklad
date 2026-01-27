<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_invitations', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->string('token')->index();
            $table->foreignId('account_id')->constrained('accounts');
            $table->foreignId('invited_by_id')->constrained('users');
            $table->foreignId('accepted_by_id')->nullable()->constrained('users');
            $table->string('email');
            $table->tinyInteger('role');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_invitations');
    }
};
