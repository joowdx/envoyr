<?php

use App\Models\Classification;
use App\Models\Office;
use App\Models\Section;
use App\Models\Source;
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
        Schema::create('documents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('code')->unique();
            $table->string('title');
            $table->foreignIdFor(Classification::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Office::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Section::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Source::class)->constrained()->cascadeOnDelete();
            $table->boolean('digital')->default(false);
            $table->boolean('directive')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['office_id', 'created_at']); // Office + date queries
            $table->index(['office_id', 'deleted_at']); // Soft delete queries by office
            $table->index('created_at'); // Date-based sorting/filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
