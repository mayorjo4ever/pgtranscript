<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notes', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['type', 'reference', 'title']);
            
            // Add new columns
            $table->date('date')->after('telegram_id');
            $table->string('preacher')->after('date');
            $table->string('topic')->after('preacher');
            $table->text('message')->after('topic');
            
            // Rename 'note' to 'message' if it exists
            // (We'll handle this separately)
        });
        
        // Rename column
        Schema::table('notes', function (Blueprint $table) {
            if (Schema::hasColumn('notes', 'note')) {
                $table->renameColumn('note', 'additional_notes');
            }
        });
    }

    public function down()
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['date', 'preacher', 'topic', 'message']);
            $table->enum('type', ['bible', 'hymn']);
            $table->string('reference');
            $table->string('title')->nullable();
        });
    }
};