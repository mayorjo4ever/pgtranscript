<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->boolean('receive_daily_verse')->default(true)->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->dropColumn('receive_daily_verse');
        });
    }
};