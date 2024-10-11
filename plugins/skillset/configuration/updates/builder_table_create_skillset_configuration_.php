<?php namespace skillset\Configuration\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetConfiguration extends Migration
{
    public function up()
    {
        Schema::create('skillset_configuration_', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('key');
            $table->string('title');
            $table->string('value');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_configuration_');
    }
}
