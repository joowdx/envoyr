<?php

use App\Models\Document;
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
            $table->string('purpose');
            $table->text('remarks')->nullable();
            
            // Document being transmitted
            $table->foreignIdFor(Document::class)->constrained()->cascadeOnDelete();
            
            // From (sender)
            $table->foreignIdFor(Office::class, 'from_office_id')->constrained('offices')->cascadeOnDelete();
            $table->foreignIdFor(Section::class, 'from_section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'from_user_id')->constrained('users')->cascadeOnDelete();
            
            // To (receiver)
            $table->foreignIdFor(Office::class, 'to_office_id')->constrained('offices')->cascadeOnDelete();
            $table->foreignIdFor(Section::class, 'to_section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'to_user_id')->constrained('users')->cascadeOnDelete();
            
            // Receiving info
            $table->timestamp('received_at')->nullable();
            $table->foreignIdFor(User::class, 'received_by_id')->nullable()->constrained('users')->cascadeOnDelete(); 
            
            // Delivery method
            $table->boolean('pick_up')->default(false);
            
            $table->timestamps();

            // Indexes for common queries
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
