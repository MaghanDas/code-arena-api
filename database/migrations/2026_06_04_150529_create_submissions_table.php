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
    Schema::create('submissions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
        $table->text('code');
        $table->string('language'); // 'javascript' | 'python' | 'php'
        $table->string('status')->default('pending'); // 'accepted' | 'wrong_answer' | 'tle'
        $table->integer('score')->default(0);
        $table->integer('runtime_ms')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('submissions');
}
};
