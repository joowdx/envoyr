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
        Schema::create('prerequisites', function (Blueprint $table) {
            // Proper pivot table - no separate primary key
            $table->ulid('action_id');
            $table->ulid('required_action_id');
            $table->timestamps();

            $table->foreign('action_id')->references('id')->on('actions')->cascadeOnDelete();
            $table->foreign('required_action_id')->references('id')->on('actions')->cascadeOnDelete();
            $table->primary(['action_id', 'required_action_id']);
            $table->index('action_id');
            $table->index('required_action_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prerequisites');
    }
};
