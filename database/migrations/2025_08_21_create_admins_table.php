<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for creating the admins table.
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
        if (Schema::hasTable('admins')) {
            return;
        }

        Schema::create('admins', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('avatar')->nullable()->comment('Admin profile picture path');
            $table->string('name')->comment('Admin full name');
            $table->string('email')->unique()->comment('Unique email address');
            $table->string('mobile')->unique()->comment('Unique mobile number');
            $table->string('password')->comment('Hashed password');
            $table->string('role')->index()->comment('Admin role (e.g., super_admin, editor)');
            $table->boolean('active')->default(true)->comment('Account status');
            $table->timestamp('last_login_at')->nullable()->comment('Last login timestamp');
            $table->string('last_login_ip')->nullable()->comment('Last login IP address');
            $table->boolean('two_factor_enabled')->default(false)->comment('Two-factor authentication status');
            $table->string('two_factor_secret')->nullable()->comment('Two-factor secret key');
            $table->rememberToken()->comment('Remember me token');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
