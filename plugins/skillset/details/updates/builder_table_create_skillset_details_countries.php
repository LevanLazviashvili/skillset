<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetDetailsCountries extends Migration
{
    public function up()
    {
        Schema::create('skillset_details_countries', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_details_countries');
    }
}
