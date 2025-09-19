<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_action', function (Blueprint $table) {
            $table->id();
            $table->ulid('process_id'); 
            $table->bigInteger('action_type_id')->unsigned(); 
            $table->timestamps();

            $table->foreign('process_id')->references('id')->on('processes')->cascadeOnDelete();
            $table->foreign('action_type_id')->references('id')->on('action_types')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_action');
    }
};