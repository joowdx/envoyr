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
            $table->boolean('electronic')->default(false);
            $table->boolean('dissemination')->default(false);
            $table->foreignIdFor(Classification::class)->constrained()->cascadeOnDelete();
            $table->ulid('user_id'); // Changed to ULID for User reference
            $table->foreignIdFor(Office::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Section::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Source::class)->nullable()->constrained()->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Add foreign key constraint for user_id
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['office_id', 'created_at']); // Office + date queries
            $table->index(['office_id', 'deleted_at']); // Soft delete queries by office
            $table->index('created_at');
            $table->index('published_at'); // Publication status queries
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
