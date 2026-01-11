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
        Schema::table('transcripts_imports', function (Blueprint $table) {
            $table->string('form_key')->after('id')->default('transcript')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transcripts_imports', function (Blueprint $table) {
            $table->dropColumn('form_key');
        });
    }
};
