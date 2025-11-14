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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('regno');
            $table->string('title',30)->nullable();           
            $table->string('surname')->nullable();           
            $table->string('firstname')->nullable();          
            $table->string('othername')->nullable();     
            $table->string('pix')->nullable();
            $table->date('appointment_date')->nullable();                  
            $table->string('mobile')->nullable(); 
            $table->string('email')->unique();
            $table->tinyInteger('status')->default(1);
            $table->string('image')->nullable();
            $table->string('password')->nullable(); 
            $table->enum('confirm',['yes','no'])->default('no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
