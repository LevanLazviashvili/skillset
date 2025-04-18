<?php namespace skillset\Conversations\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetConversationsMessages extends Migration
{
    public function up()
    {
        Schema::create('skillset_conversations_messages', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('conversation_id');
            $table->integer('user_id');
            $table->text('message');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_conversations_messages');
    }
}
