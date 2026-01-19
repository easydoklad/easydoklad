<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispatched_webhooks', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->string('event');
            $table->foreignId('webhook_id')->constrained('webhooks');
            $table->tinyInteger('attempts');
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('payload')->nullable();
            $table->json('failures')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatched_webhooks');
    }
};
