<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'uuid')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->uuid()->nullable()->after('id')->index();
        });

        DB::statement('UPDATE users SET uuid = UUID()');

        Schema::table('users', function (Blueprint $table) {
            $table->uuid()->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
