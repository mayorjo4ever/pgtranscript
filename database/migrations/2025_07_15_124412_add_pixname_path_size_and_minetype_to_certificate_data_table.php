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
            $table->tinyInteger('name_swapped')->default(0);
            $table->string('pix_name')->nullable();
            $table->string('filename')->nullable();
            $table->string('pix_path')->nullable();
            $table->bigInteger('size')->nullable();
            $table->string('mime_type')->nullable();
            $table->year('year')->after('approve_date_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_data', function (Blueprint $table) {
           $table->dropColumn('name_swapped');
           $table->dropColumn('pix_name');
           $table->dropColumn('filename');
           $table->dropColumn('pix_path');
           $table->dropColumn('size');
           $table->dropColumn('mime_type');
           $table->dropColumn('year');
        });
    }
};
