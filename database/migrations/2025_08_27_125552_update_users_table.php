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
        Schema::table('users', function (Blueprint $table) {
            $table->string('appno')->nullable();
            $table->string('regno')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->date('dob')->nullable();
            $table->date('first_reg_date')->nullable(); 
            $table->date('date_approved')->nullable();
            $table->integer('fact_id')->nullable();
            $table->integer('dept_id')->nullable();
            $table->integer('programme_id')->nullable();  
            $table->string('marital_status')->nullable();            
            $table->string('phone')->nullable();
            $table->string('department')->nullable();
            $table->string('programme')->nullable();
            $table->string('level')->nullable();
            $table->string('admission_session')->nullable();
            $table->string('category')->nullable();            
            $table->string('state_of_origin')->nullable();
            $table->string('local_government')->nullable();
            $table->string('ward')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('Active');
            // Banking details
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bvn')->nullable();
            $table->string('nin')->nullable();
            $table->string('pvc_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'appno',
                'regno',
                'first_name',
                'middle_name',
                'last_name',
                'gender',
                'dob',
                'first_reg_date',
                'date_approved',
                'fact_id',
                'dept_id',
                'programme_id',
                'marital_status',
                'phone',
                'department',
                'programme',
                'level',
                'admission_session',
                'category',
                'state_of_origin',
                'local_government',
                'ward',
                'address',
                'status',
                'account_number',
                'account_name',
                'bank_name',
                'bvn',
                'nin',
                'pvc_number',
            ]);
        });
    }
};
