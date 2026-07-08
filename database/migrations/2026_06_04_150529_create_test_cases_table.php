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
    Schema::create('test_cases', function (Blueprint $table) {
        $table->id();
        $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
        $table->text('input');
        $table->text('expected_output');
        $table->boolean('is_hidden')->default(false);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('test_cases');
}
};
