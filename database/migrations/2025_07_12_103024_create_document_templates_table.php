<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->foreignId('account_id')->nullable()->constrained('accounts');
            $table->string('document_type');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('package');
            $table->string('installation_path');
            $table->json('options');
            $table->boolean('is_default');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
