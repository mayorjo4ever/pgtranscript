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
        Schema::create('transcript_reports', function (Blueprint $table) {
            $table->id();
            $table->string('regno'); 
            $table->string('name'); 
            $table->string('fact_id',30)->nullable(); 
            $table->string('dept_id',30)->nullable();
            $table->string('programme');
            $table->date('first_reg_date')->nullable();
            $table->date('approve_date');
            $table->date('dob')->nullable();            
            $table->integer('author_id')->nullable();
            $table->string('created_by',30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcript_reports');
    }
};
