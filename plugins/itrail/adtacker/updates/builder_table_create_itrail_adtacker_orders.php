<?php namespace ItRail\AdTacker\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateItrailAdtackerOrders extends Migration
{
    public function up()
    {
        Schema::create('itrail_adtacker_orders', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('code');
            $table->integer('user_id');
            $table->text('address');
            $table->integer('status_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('itrail_adtacker_orders');
    }
}
