<?php namespace skillset\Conversations\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetConversationsUsers extends Migration
{
    public function up()
    {
        Schema::table('skillset_conversations_users', function($table)
        {
            $table->integer('user_id')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_conversations_users', function($table)
        {
            $table->integer('user_id')->default(null)->change();
        });
    }
}
