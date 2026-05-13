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
            $table->string('printout_url')->nullable()->after('request_time_dt');
             $table->string('memo_url')->nullable()->after('printout_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transcripts_requests', function (Blueprint $table) {
            $table->dropColumn(['printout_url', 'memo_url']);
        });
    }
};
