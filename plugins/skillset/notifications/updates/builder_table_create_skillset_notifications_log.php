<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetNotificationsLog extends Migration
{
    public function up()
    {
        Schema::create('skillset_notifications_log', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id');
            $table->integer('template_id');
            $table->smallInteger('seen')->nullable()->default(0);
            $table->string('additional_data', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_notifications_log');
    }
}
