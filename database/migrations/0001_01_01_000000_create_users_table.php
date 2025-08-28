<?php

use App\Enums\UserRole;
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
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary(); 
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            $table->string('role')->default(UserRole::USER->value);
            $table->ulid('office_id');
            $table->ulid('section_id')->nullable();
            $table->string('designation')->nullable();

            // Invitation fields
            $table->string('invitation_token')->nullable()->unique();
            $table->timestamp('invitation_expires_at')->nullable();
            $table->timestamp('invitation_accepted_at')->nullable();
            $table->ulid('invited_by')->nullable(); 

            // Deactivation fields
            $table->timestamp('deactivated_at')->nullable();
            $table->ulid('deactivated_by')->nullable(); // Changed to ULID

            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
            $table->timestamp('email_verified_at')->nullable();
        });

        // Add foreign key constraints for self-referencing columns
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('invited_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deactivated_by')->references('id')->on('users')->nullOnDelete();
        });

        // Note: Foreign keys to offices and sections will be added by their respective migrations

        // Create password reset tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Create sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->ulid('user_id')->nullable()->index(); // Changed to ULID to match users table
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();

            // Add foreign key constraint for user_id
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
