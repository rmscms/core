<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rms_notification_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');
            // If null => immediate (OFF). If set => either one-off or recurring.
            $table->timestamp('deliver_at')->nullable();
            // Recurrence RFC-like rule or simplified JSON: {"type":"weekly","weekday":1,"time":"09:30"} etc.
            $table->json('recurrence')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->string('timezone', 64)->nullable();
            $table->string('status', 20)->default('active'); // active, paused, canceled, completed

            $table->timestamps();

            $table->foreign('notification_id')->references('id')->on('rms_notifications')->onDelete('cascade');
            $table->index(['notification_id']);
            $table->index(['next_run_at']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rms_notification_schedules');
    }
};
