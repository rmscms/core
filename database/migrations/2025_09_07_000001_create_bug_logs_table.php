<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bug_logs', function (Blueprint $table) {
            $table->id();
            
            // اطلاعات اصلی خطا
            $table->string('title')->comment('عنوان خطا');
            $table->text('error_message')->comment('پیام خطای کامل');
            $table->string('error_code', 50)->nullable()->comment('کد خطا');
            $table->string('file_path', 500)->nullable()->comment('مسیر فایل خطا');
            $table->unsignedInteger('line_number')->nullable()->comment('شماره خط خطا');
            
            // Context و Environment
            $table->string('request_url', 1000)->nullable()->comment('URL درخواست');
            $table->enum('request_method', ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->comment('کاربر مربوطه');
            $table->string('session_id', 100)->nullable()->comment('Session ID');
            $table->text('user_agent')->nullable()->comment('مرورگر کاربر');
            $table->string('ip_address', 45)->nullable()->comment('IP Address');
            
            // Stack Trace و Debug Info
            $table->longText('stack_trace')->nullable()->comment('Stack trace کامل');
            $table->json('request_data')->nullable()->comment('داده‌های درخواست');
            $table->json('debug_info')->nullable()->comment('اطلاعات debug اضافی');
            
            // Bug Tracking Fields
            $table->enum('severity', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])->default('MEDIUM');
            $table->string('category', 100)->nullable()->comment('دسته‌بندی خطا');
            
            // Status Management
            $table->enum('status', ['NEW', 'IN_PROGRESS', 'FIXED', 'CONFIRMED', 'CLOSED'])->default('NEW');
            $table->boolean('ai_fixed')->default(false)->comment('AI فیکس کرده؟');
            $table->text('ai_fix_description')->nullable()->comment('توضیحات فیکس AI');
            $table->json('ai_fix_files')->nullable()->comment('فایل‌های تغییر یافته توسط AI');
            $table->boolean('human_confirmed')->default(false)->comment('انسان تأیید کرده؟');
            $table->text('human_confirmation_notes')->nullable()->comment('یادداشت‌های تأیید انسان');
            
            // Timestamps
            $table->timestamp('occurred_at')->useCurrent()->comment('زمان وقوع خطا');
            $table->timestamp('ai_fixed_at')->nullable()->comment('زمان فیکس AI');
            $table->timestamp('human_confirmed_at')->nullable()->comment('زمان تأیید انسان');
            $table->timestamps();
            
            // Indexes برای بهینه‌سازی جستجو
            $table->index('status');
            $table->index('severity'); 
            $table->index('category');
            $table->index('occurred_at');
            $table->index('ai_fixed');
            $table->index('human_confirmed');
            $table->index(['status', 'severity']); // Composite index
            $table->index(['ai_fixed', 'human_confirmed']); // برای فیلتر needsConfirmation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_logs');
    }
};
