<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOrdersSubServices extends Migration
{
    public function up()
    {
        Schema::table('skillset_orders_sub_services', function($table)
        {
            $table->integer('quantity')->default(0)->change();
            $table->smallInteger('status_id')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_orders_sub_services', function($table)
        {
            $table->integer('quantity')->default(null)->change();
            $table->smallInteger('status_id')->default(null)->change();
        });
    }
}
