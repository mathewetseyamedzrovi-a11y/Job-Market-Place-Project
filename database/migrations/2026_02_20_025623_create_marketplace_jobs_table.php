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
    Schema::create('marketplace_jobs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('poster_id')->constrained('users');
        $table->string('title');
        $table->text('description');
        $table->foreignId('category_id')->constrained();
        $table->decimal('budget', 10, 2);
        $table->string('location');
        $table->decimal('latitude', 10, 8)->nullable();
        $table->decimal('longitude', 11, 8)->nullable();
        $table->string('urgency')->default('normal');
        $table->string('duration')->nullable();
        $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_jobs');
    }
};
