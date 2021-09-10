<?php namespace ItRail\AdTacker\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBackendUsers extends Migration
{
    public function up()
    {
        Schema::table('backend_users', function($table)
        {
            $table->string('name')->nullable()->after('id');
            $table->string('phone')->nullable()->after('login');
            $table->text('address')->nullable()->after('email');
            $table->string('nid')->nullable()->after('email');
            $table->integer('referral_id')->nullable()->after('email');

            $table->index('phone');
            $table->index('email');
        });
    }
    
    public function down()
    {
        Schema::table('backend_users', function($table)
        {
            $table->dropColumn('name');
            $table->dropColumn('phone');
            $table->dropColumn('address');
            $table->dropColumn('nid');
            $table->dropColumn('referral_id');

            $table->unique('email');
            $table->dropIndex(['email']);
        });
    }
}
