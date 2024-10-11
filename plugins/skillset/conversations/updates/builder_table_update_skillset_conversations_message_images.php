<?php namespace skillset\Conversations\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetConversationsMessageImages extends Migration
{
    public function up()
    {
        Schema::table('skillset_conversations_message_images', function($table)
        {
            $table->string('path', 255)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_conversations_message_images', function($table)
        {
            $table->string('path', 255)->default('NULL')->change();
            $table->string('thumb', 255)->default('NULL')->change();
        });
    }
}
