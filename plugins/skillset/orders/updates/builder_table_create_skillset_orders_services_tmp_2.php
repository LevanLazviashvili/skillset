<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetOrdersServicesTmp2 extends Migration
{
    public function up()
    {
        Schema::create('skillset_orders_services_tmp', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->decimal('amount', 10, 2);
            $table->integer('order_id')->nullable()->default(0);
            $table->string('title');
            $table->integer('unit_id');
            $table->decimal('unit_price', 10, 2);
            $table->integer('offer_id')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_orders_services_tmp');
    }
}
