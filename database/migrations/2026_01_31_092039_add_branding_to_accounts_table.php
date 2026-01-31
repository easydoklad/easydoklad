<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreignId('wide_logo_id')->after('invoice_mail_message')->nullable()->constrained('uploads');
            $table->foreignId('square_logo_id')->after('invoice_mail_message')->nullable()->constrained('uploads');
            $table->json('mail_configuration')->after('invoice_mail_message')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wide_logo_id');
            $table->dropConstrainedForeignId('square_logo_id');
            $table->dropColumn('mail_configuration');
        });
    }
};
