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
        Schema::create('office_actions', function (Blueprint $table) {
            $table->id();
            $table->ulid('office_id');
            $table->foreignId('action_type_id')->constrained()->cascadeOnDelete();
            $table->ulid('user_id');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('office_id')->references('id')->on('offices')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_actions');
    }
};
