<?php namespace ItRail\AdTacker\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateItrailAdtackerSlides extends Migration
{
    public function up()
    {
        Schema::create('itrail_adtacker_slides', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->integer('sort_order');
            $table->boolean('is_published')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('itrail_adtacker_slides');
    }
}
