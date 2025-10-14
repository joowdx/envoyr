<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_actions', function (Blueprint $table) {
            $table->id();
            $table->ulid('process_id');
            $table->unsignedBigInteger('action_type_id');
            $table->integer('sequence_order')->default(1);
            $table->datetime('completed_at')->nullable();
            $table->ulid('completed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('process_id')->references('id')->on('processes')->cascadeOnDelete();
            $table->foreign('action_type_id')->references('id')->on('action_types')->cascadeOnDelete();
            $table->foreign('completed_by')->references('id')->on('users')->nullOnDelete();
            
            $table->unique(['process_id', 'action_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_actions');
    }
};