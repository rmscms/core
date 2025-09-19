<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for creating the users table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Drop existing users table if it exists (Laravel default)
        Schema::dropIfExists('users');
        
        // Create our enhanced users table
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('name')->comment('User full name');
            $table->string('email')->unique()->comment('Unique email address');
            $table->timestamp('email_verified_at')->nullable()->comment('Email verification timestamp');
            $table->string('password')->comment('Hashed password');
            $table->integer('group_id')->default(4)->comment('User group (1=Admin, 2=Moderator, 3=Editor, 4=Guest)');
            $table->boolean('active')->default(true)->comment('Account status');
            $table->boolean('email_notifications')->default(true)->comment('Email notifications enabled');
            $table->date('birth_date')->nullable()->comment('Date of birth');
            $table->string('avatar')->nullable()->comment('Avatar image path');
            $table->rememberToken()->comment('Remember me token');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp');
            
            // Add indexes for better performance
            $table->index('group_id');
            $table->index('active');
            $table->index('email_verified_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};