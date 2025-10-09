
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
        Schema::create('action_type_dependencies', function (Blueprint $table) {
            $table->id();
            $table->unique(['action_type_id', 'prerequisite_action_type_id'], 'unique_dependency');
            $table->timestamps();

            $table->foreignId('action_type_id')->constrained('action_types')->cascadeOnDelete();
            $table->foreignId('prerequisite_action_type_id')->constrained('action_types')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_type_dependencies');
    }
};
