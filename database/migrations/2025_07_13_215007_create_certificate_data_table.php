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
        Schema::create('certificate_data', function (Blueprint $table) {
            $table->id();
            $table->string('regno');
            $table->string('raw_name');
            $table->string('raw_programme');            
            $table->integer('approve_date_id');
            $table->string('name')->nullable();
            $table->integer('degree_id')->nullable();
            $table->integer('faculty_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->integer('programme_id')->nullable();
            $table->string('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('completed')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cerificate_data');
    }
};
