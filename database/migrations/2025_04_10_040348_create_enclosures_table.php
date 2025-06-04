<?php

use App\Models\Document;
use App\Models\Transmittal;
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
        Schema::create('enclosures', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Document::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Transmittal::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['document_id', 'transmittal_id'], 'unique_document_transmittal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enclosures');
    }
};
