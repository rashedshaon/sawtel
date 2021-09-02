<?php namespace ItRail\AdTacker\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateItrailAdtackerOrderItems extends Migration
{
    public function up()
    {
        Schema::create('itrail_adtacker_order_items', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('order_id');
            $table->integer('product_id');
            $table->string('name');
            $table->integer('quantity');
            $table->string('price');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('itrail_adtacker_order_items');
    }
}
