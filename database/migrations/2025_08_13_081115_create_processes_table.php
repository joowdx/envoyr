<?php

use App\Models\Document;
use App\Models\Transmittal;
use App\Models\User;
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
        Schema::create('processes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Document::class)->constrained('documents')->cascadeOnDelete();
            $table->foreignIdFor(Transmittal::class)->constrained('transmittals')->cascadeOnDelete();
            $table->ulid('user_id'); // Changed to ULID for User reference
            $table->timestamp('processed_at')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            // Add foreign key constraint for user_id
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processes');
    }
};
