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
        Schema::table('transcript_printouts', function (Blueprint $table) {
            $table->bigInteger('request_id')->after('purpose')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transcript_printouts', function (Blueprint $table) {
            $table->dropColumn('request_id');
        });
    }
};
