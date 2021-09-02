<?php namespace ItRail\AdTacker\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateItrailAdtackerWithdrawRequests extends Migration
{
    public function up()
    {
        Schema::create('itrail_adtacker_withdraw_requests', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('bank_id');
            $table->string('account_no');
            $table->string('amount');
            $table->integer('status')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('itrail_adtacker_withdraw_requests');
    }
}
