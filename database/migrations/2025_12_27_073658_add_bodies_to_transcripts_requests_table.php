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
            $table->enum('bodies',['postgraduate','undergraduate'])->after('id')->default('postgraduate');
            $table->integer('progression')->after('bodies')->default(0);
            $table->string('last_viewer')->after('progression')->nullable();
            $table->dateTime('last_viewed')->after('last_viewer')->nullable();
            $table->tinyInteger('mail_sent')->default(0);
            $table->string('sent_by')->nullable();
            $table->dateTime('date_sent')->nullable();
            $table->integer('sent_count')->default(0);
            $table->string('last_sent_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transcripts_requests', function (Blueprint $table) {
            $table->dropcolumn(['bodies','progression','last_viewer','last_viewed']);
            $table->dropcolumn(['mail_sent','sent_by','date_sent','sent_count','last_sent_email']);
        });
    }
};
