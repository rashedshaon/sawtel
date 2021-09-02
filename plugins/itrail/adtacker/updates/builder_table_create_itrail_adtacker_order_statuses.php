<?php namespace ItRail\AdTacker\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateItrailAdtackerOrderStatuses extends Migration
{
    public function up()
    {
        Schema::create('itrail_adtacker_order_statuses', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('color');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('itrail_adtacker_order_statuses');
    }
}
