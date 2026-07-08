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
    Schema::create('contest_participants', function (Blueprint $table) {
        $table->id();
        $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->integer('total_score')->default(0);
        $table->integer('rank')->nullable();
        $table->timestamp('joined_at')->useCurrent();
        $table->unique(['contest_id', 'user_id']); // can't join twice
    });
}

public function down(): void
{
    Schema::dropIfExists('contest_participants');
}
};
