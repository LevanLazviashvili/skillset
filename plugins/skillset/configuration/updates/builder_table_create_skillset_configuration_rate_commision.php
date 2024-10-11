<?php namespace skillset\Configuration\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetConfigurationRateCommision extends Migration
{
    public function up()
    {
        Schema::create('skillset_configuration_rate_commission', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title', 255);
            $table->integer('rate');
            $table->decimal('percent', 10, 2);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_configuration_rate_commision');
    }
}
