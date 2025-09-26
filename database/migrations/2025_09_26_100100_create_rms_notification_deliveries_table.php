<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rms_notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');
            $table->string('channel', 50); // email, telegram, webhook, push (disabled by default)
            $table->string('status', 20)->default('pending'); // pending, queued, sent, failed, canceled
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('meta')->nullable(); // channel-specific payload/settings

            $table->timestamps();

            $table->foreign('notification_id')->references('id')->on('rms_notifications')->onDelete('cascade');
            $table->index(['notification_id']);
            $table->index(['channel']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rms_notification_deliveries');
    }
};
