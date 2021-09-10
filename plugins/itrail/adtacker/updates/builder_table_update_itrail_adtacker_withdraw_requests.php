<?php namespace ItRail\AdTacker\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateItrailAdtackerWithdrawRequests extends Migration
{
    public function up()
    {
        Schema::table('itrail_adtacker_withdraw_requests', function($table)
        {
            $table->integer('user_id')->after('id');
        });
    }
    
    public function down()
    {
        Schema::table('itrail_adtacker_withdraw_requests', function($table)
        {
            $table->dropColumn('user_id');
        });
    }
}
