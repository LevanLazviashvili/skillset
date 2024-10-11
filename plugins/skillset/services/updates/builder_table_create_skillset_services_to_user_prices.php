<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetServicesToUserPrices extends Migration
{
    public function up()
    {
        Schema::create('skillset_services_to_user_prices', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('services_to_user_id');
            $table->string('title');
            $table->integer('amount');
            $table->integer('unit_id');
            $table->double('price_from', 10, 0);
            $table->double('price_to', 10, 0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_services_to_user_prices');
    }
}
