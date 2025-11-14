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
        Schema::create('transcripts', function (Blueprint $table) {
            $table->id();
            $table->string('regno');           
            $table->string('code',20);
            $table->string('title');
            $table->integer('units');
            $table->string('type',20);
            $table->string('level',20)->nullable();
            $table->string('semester',20)->nullable();
            $table->double('score');
            $table->boolean('starred')->default(false);            
            $table->boolean('completed')->default(false);
            $table->date('approve_date');
            $table->integer('author_id');
            $table->string('created_by');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcripts');
    }
};
