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
    Schema::create('contest_challenges', function (Blueprint $table) {
        $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
        $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
        $table->primary(['contest_id', 'challenge_id']);
    });
}

public function down(): void
{
    Schema::dropIfExists('contest_challenges');
}
};
