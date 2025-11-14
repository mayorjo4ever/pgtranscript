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
        Schema::create('transcript_printouts', function (Blueprint $table) {
            $table->id();
            $table->string('regno');             
            $table->date('approve_date');
            $table->string('type',30); 
            $table->enum('purpose',['convocation','request'])->nullable(); 
            $table->boolean('printed')->default(false);
            $table->integer('print_count')->default(0);
            $table->integer('author_id')->nullable();
            $table->string('created_by',30)->nullable();
            $table->string('printed_by',30)->nullable();   
            $table->string('sec_id',30);   
            $table->string('dean_id',30);               
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcript_printouts');
    }
};
