<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetOrders extends Migration
{
    public function up()
    {
        Schema::create('skillset_orders_', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('service_id');
            $table->integer('customer_id');
            $table->integer('master_id');
            $table->smallInteger('status_id');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->smallInteger('rate');
            $table->string('comment');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_orders_');
    }
}
