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
       Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id'); // User's telegram ID
            $table->enum('type', ['bible', 'hymn']); // Type of note
            $table->string('reference'); // e.g., "John 3:16" or "Hymn 25"
            $table->string('title')->nullable(); // Optional note title/topic
            $table->text('note'); // The actual note content
            $table->timestamps();

            $table->index(['telegram_id', 'type']);
            $table->index('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
