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
             $table->dateTime('request_time_dt')->after('request_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('transcripts_requests', function (Blueprint $table) {
             $table->dropColumn('request_time_dt');
        });
    }
};
