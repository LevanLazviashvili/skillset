<?php namespace skillset\Conversations\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetConversationsMessageImages extends Migration
{
    public function up()
    {
        Schema::create('skillset_conversations_message_images', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('conversation_id');
            $table->string('image', 255)->nullable();
            $table->string('thumb', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_conversations_message_images');
    }
}
