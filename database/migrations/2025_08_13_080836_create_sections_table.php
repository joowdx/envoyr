<?php

use App\Models\Office;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->foreignIdFor(Office::class)->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('head_name')->nullable();
            $table->string('designation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('section_id')->references('id')->on('sections')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
