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
            $table->ulid('user_id'); 
            $table->ulid('office_id')->nullable();
            $table->ulid('classification_id')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->timestamp('processed_at')->nullable();
            

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('office_id')->references('id')->on('offices')->cascadeOnDelete();
            $table->foreign('classification_id')->references('id')->on('classifications')->cascadeOnDelete();
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
