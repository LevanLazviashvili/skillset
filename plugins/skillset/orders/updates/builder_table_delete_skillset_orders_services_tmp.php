<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteSkillsetOrdersServicesTmp extends Migration
{
    public function up()
    {
        Schema::dropIfExists('skillset_orders_services_tmp');
    }
    
    public function down()
    {
        Schema::create('skillset_orders_services_tmp', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('amount');
            $table->integer('offer_id');
            $table->integer('order_id');
            $table->integer('title');
            $table->integer('unit_id');
            $table->integer('unit_price');
        });
    }
}
