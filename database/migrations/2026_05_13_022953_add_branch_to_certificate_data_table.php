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
            $table->string('branch')->nullable()->after('degree_class');
            $table->string('created_by')->nullable()->after('branch');
            $table->string('updated_by')->nullable()->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_data', function (Blueprint $table) {
            $table->dropColumn('branch');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');   
        });
    }
};
