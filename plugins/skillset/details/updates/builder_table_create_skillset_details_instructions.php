<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetDetailsInstructions extends Migration
{
    public function up()
    {
        Schema::create('skillset_details_instructions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title')->nullable();
            $table->string('video_url')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_details_instructions');
    }
}
