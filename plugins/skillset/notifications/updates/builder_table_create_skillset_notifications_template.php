<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetNotificationsTemplate extends Migration
{
    public function up()
    {
        Schema::create('skillset_notifications_template', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title');
            $table->string('description');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_notifications_template');
    }
}
