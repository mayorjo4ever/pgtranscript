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
        Schema::create('hymns', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // Hymn number (1, 2, 3, etc.)
            $table->string('title'); // Hymn title (e.g., "Only Believe")
            $table->text('lyrics'); // Full hymn lyrics
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hymns');
    }
};
