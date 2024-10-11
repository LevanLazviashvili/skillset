<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetOrdersSubServices extends Migration
{
    public function up()
    {
        Schema::create('skillset_orders_sub_services', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('order_id');
            $table->integer('sub_service_id');
            $table->integer('quantity');
            $table->smallInteger('status_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_orders_sub_services');
    }
}
