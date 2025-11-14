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
        Schema::table('certificate_data', function (Blueprint $table) {
            $table->boolean('status_uploaded')->after('completed')->default(false);
            $table->dateTime('date_status_uploaded')->after('status_uploaded')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_data', function (Blueprint $table) {
            $table->dropColumn(['status_uploaded','date_status_uploaded']);
        });
    }
};
