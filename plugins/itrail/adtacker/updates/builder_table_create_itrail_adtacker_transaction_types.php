<?php namespace ItRail\AdTacker\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateItrailAdtackerTransactionTypes extends Migration
{
    public function up()
    {
        Schema::create('itrail_adtacker_transaction_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('action');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('itrail_adtacker_transaction_types');
    }
}
