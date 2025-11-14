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
        Schema::create('transcript_cover_letters', function (Blueprint $table) {
            $table->id();
            $table->string('regno',30);
            $table->string('name');
            $table->bigInteger('request_id');
            $table->string('wes_ref_no')->nullable();
            $table->text('destination_address');
            $table->string('sec_id');
            $table->integer('author_id')->nullable();
            $table->string('created_by',30)->nullable();            
            $table->boolean('printed')->default(false);
            $table->integer('print_count')->default(0);
            $table->string('printed_by')->nullable();                    
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcript_cover_letters');
    }
};
