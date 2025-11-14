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
        Schema::create('transcripts_requests', function (Blueprint $table) {
            $table->id();            
            $table->string('request_time');
            $table->string('request_status')->default('created');
            $table->enum('request_method',['online','manual'])->default('online');
            $table->string('request_email')->nullable();
            $table->string('applicant_email');
            $table->string('regno');
            $table->string('surname');
            $table->string('middle_name');
            $table->string('year_of_entry')->nullable();
            $table->string('year_of_graduation')->nullable();
            $table->string('degree_awarded')->nullable();
            $table->string('faculty')->nullable();
            $table->string('department')->nullable();
            $table->string('request_type')->nullable(); // transcript / verification / english proficiency
            $table->string('request_purpose')->nullable();  // official / student copy
            $table->string('reference_number')->nullable();
            $table->text('destination_address')->nullable();
            $table->string('rrr')->nullable();
            $table->string('mode_of_postage')->nullable();
            $table->string('applicant_phone')->nullable();
            $table->string('courier_agent')->nullable();
            $table->string('receiving_body_email')->nullable();
            $table->enum('obtained_transcript_before',['yes','no'])->nullable();
            $table->string('date_obtained')->nullable();
            $table->string('certificate_url')->nullable();
            $table->string('rrr_receipt_url')->nullable();
            $table->string('courier_receipt_url')->nullable();
            $table->string('pgschool_receipt_url')->nullable();
            $table->string('applicant_dob')->nullable();
            $table->string('applicant_dob_cert')->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcripts_requests');
    }
};
