<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('steps', function (Blueprint $table) {
            // Proper pivot table - uses composite primary key
            $table->ulid('process_id');
            $table->ulid('action_id');
            $table->integer('sequence_order')->default(1);
            $table->datetime('completed_at')->nullable();
            $table->ulid('completed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('process_id')->references('id')->on('processes')->cascadeOnDelete();
            $table->foreign('action_id')->references('id')->on('actions')->cascadeOnDelete();
            $table->foreign('completed_by')->references('id')->on('users')->nullOnDelete();

            $table->primary(['process_id', 'action_id']);
            $table->index(['process_id', 'sequence_order']);
            $table->index(['action_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('steps');
    }
};
