<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('temporary_uploads', function (Blueprint $table) {
            $table->string('client_name')->nullable()->after('scope');
        });
    }

    public function down(): void
    {
        Schema::table('temporary_uploads', function (Blueprint $table) {
            $table->dropColumn('client_name');
        });
    }
};
