<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailboxInboundEmailsTable extends Migration
{
    public function up(): void
    {
        Schema::create('mailbox_inbound_emails', function (Blueprint $table) {
            $table->id();
            $table->string('message_id');
            $table->longText('message');
            $table->nullableTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailbox_inbound_emails');
    }
}
