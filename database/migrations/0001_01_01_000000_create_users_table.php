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
            $table->id();
            $table->string('name')->nullable(); 
            $table->string('email')->unique();
            $table->string('password')->nullable(); 
            $table->string('avatar')->nullable();
            $table->string('role')->default(UserRole::USER->value);
            $table->ulid('office_id')->nullable();
            $table->ulid('section_id')->nullable();
            $table->string('designation')->nullable();

            // Invitation fields
            $table->string('invitation_token')->nullable()->unique();
            $table->timestamp('invitation_expires_at')->nullable();
            $table->timestamp('invitation_accepted_at')->nullable();
            $table->foreignId('invited_by')->nullable()->constrained('users');
            
            // Approval fields
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            
            // Deactivation fields
            $table->timestamp('deactivated_at')->nullable();
            $table->foreignId('deactivated_by')->nullable()->constrained('users');
            
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
            $table->timestamp('email_verified_at')->nullable();
        });

        
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('office_id')->references('id')->on('offices');
            $table->foreign('section_id')->references('id')->on('sections');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
