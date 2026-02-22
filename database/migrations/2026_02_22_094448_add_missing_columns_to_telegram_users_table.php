<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            // Add chat_id if it doesn't exist
            if (!Schema::hasColumn('telegram_users', 'chat_id')) {
                $table->bigInteger('chat_id')->after('telegram_id')->nullable();
            }
            
            // Add last_name if it doesn't exist
            if (!Schema::hasColumn('telegram_users', 'last_name')) {
                $table->string('last_name')->after('first_name')->nullable();
            }
            
            // Add is_active if it doesn't exist
            if (!Schema::hasColumn('telegram_users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function down()
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            if (Schema::hasColumn('telegram_users', 'chat_id')) {
                $table->dropColumn('chat_id');
            }
            if (Schema::hasColumn('telegram_users', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('telegram_users', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};