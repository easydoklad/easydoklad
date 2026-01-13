<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('document_templates', 'uuid')) {
            return;
        }

        Schema::table('document_templates', function (Blueprint $table) {
            $table->uuid()->nullable()->after('id')->index();
        });

        DB::statement('UPDATE document_templates SET uuid = UUID()');

        Schema::table('document_templates', function (Blueprint $table) {
            $table->uuid()->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
