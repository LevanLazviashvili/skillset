<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetOrdersAdverts extends Migration
{
    public function up()
    {
        Schema::create('skillset_orders_adverts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->morphs('advertable');
            $table->decimal('price')->unsigned()->default(0);
            $table->decimal('total_price')->unsigned()->default(0);
            $table->decimal('bank_percent')->unsigned()->default(0);
            $table->decimal('bank_percent_amount')->unsigned()->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_orders_adverts');
    }
}