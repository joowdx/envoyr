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
        Schema::create('offices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('acronym');
            $table->string('head_name')->nullable();
            $table->string('designation')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });


        Schema::table('users', function (Blueprint $table) {
            $table->ulid('office_id')->constrained('offices')->cascadeOnDelete()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
