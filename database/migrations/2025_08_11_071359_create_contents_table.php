<?php

use App\Models\Attachment;
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
        Schema::create('contents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->smallInteger('sort')->default(0);
            $table->string('title')->index();
            $table->jsonb('context')->nullable();
            $table->jsonb('file')->nullable();
            $table->jsonb('path')->nullable();
            $table->string('hash', 64)->nullable();
            $table->foreignIdFor(Attachment::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
