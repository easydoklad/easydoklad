<?php

use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->uuid()->nullable()->after('id')->index();
        });

        if (Payment::query()->count() > 0) {
            DB::statement('UPDATE payments SET uuid = UUID()');
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->uuid()->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
