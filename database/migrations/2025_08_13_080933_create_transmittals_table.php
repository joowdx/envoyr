<?php

use App\Models\Document;
use App\Models\Process;
use App\Models\Office;
use App\Models\Section;
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
        Schema::create('transmittals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('code')->unique();
            $table->foreignIdFor(Process::class)->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('pick_up')->default(false);
            $table->foreignIdFor(Document::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Office::class, 'from_office_id')->constrained('offices')->cascadeOnDelete();
            $table->foreignIdFor(Section::class, 'from_section_id')->nullable()->constrained('sections')->cascadeOnDelete();
            $table->ulid('from_user_id')->nullable(); 
            $table->foreignIdFor(Office::class, 'to_office_id')->constrained('offices')->cascadeOnDelete();
            $table->foreignIdFor(Section::class, 'to_section_id')->nullable()->constrained('sections')->cascadeOnDelete();
            $table->ulid('to_user_id')->nullable(); 
            $table->ulid('liaison_id')->nullable(); 
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            
            $table->foreign('from_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('to_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('liaison_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['to_office_id', 'received_at']);
            $table->index(['document_id', 'received_at']);
            $table->index(['from_office_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transmittals');
    }
};
