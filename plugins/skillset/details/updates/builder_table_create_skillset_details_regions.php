<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetDetailsRegions extends Migration
{
    public function up()
    {
        Schema::create('skillset_details_regions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('country_id');
            $table->integer('title');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_details_regions');
    }
}
