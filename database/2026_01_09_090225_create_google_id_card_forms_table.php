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
        Schema::create('google_id_card_forms', function (Blueprint $table) {
            $table->id();
            $table->string('request_time')->nullable(); 
            $table->string('request_status')->default('applied');
            $table->string('request_email'); 
            $table->string('regno');
            $table->string('fullname');
            $table->string('phone')->nullable();
            $table->string('entry_session')->nullable();
            $table->string('degree')->nullable();
            $table->string('programme')->nullable();
            $table->string('faculty')->nullable();
            $table->string('department')->nullable();
            $table->text('passport')->nullable();
            $table->text('signature')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_id_card_forms');
    }
};
