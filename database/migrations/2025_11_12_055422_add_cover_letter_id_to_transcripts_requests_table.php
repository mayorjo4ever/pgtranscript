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
        Schema::table('transcripts_requests', function (Blueprint $table) {
            $table->bigInteger('transcript_cover_letter_id')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transcripts_requests', function (Blueprint $table) {
            $table->dropColumn('transcript_cover_letter_id');
        });
    }
};
