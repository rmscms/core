<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rms_notifications', function (Blueprint $table) {
            $table->id();
            // Polymorphic target: user/admin/partner/system
            $table->string('notifiable_type')->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable();
            // Optional account/tenant scope
            $table->unsignedBigInteger('account_id')->nullable();
            // Type and severity
            $table->string('category', 50)->default('general'); // info, warning, error, success, promo, system, etc.
            $table->string('title')->nullable();
            $table->text('message');
            $table->json('meta')->nullable();
            // Read state for in-app
            $table->timestamp('read_at')->nullable();
            // Immediate vs scheduled baseline time
            $table->timestamp('deliver_at')->nullable();
            // Flags
            $table->boolean('is_broadcast')->default(false); // e.g., send to all users in scope
            $table->string('status', 20)->default('created'); // created, scheduled, sent, partially_sent, failed, canceled
            // Idempotency key to prevent duplicates on retries
            $table->string('idempotency_key', 64)->nullable()->index();

            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index(['account_id']);
            $table->index(['deliver_at']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rms_notifications');
    }
};
