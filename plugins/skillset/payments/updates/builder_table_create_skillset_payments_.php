<?php namespace skillset\Payments\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetPayments extends Migration
{
    public function up()
    {
        Schema::create('skillset_payments_', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_payments_');
    }
}
