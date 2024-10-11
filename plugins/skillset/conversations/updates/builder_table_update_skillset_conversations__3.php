<?php namespace skillset\Conversations\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetConversations3 extends Migration
{
    public function up()
    {
        Schema::table('skillset_conversations_', function($table)
        {
            $table->integer('conversation_admin_id');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_conversations_', function($table)
        {
            $table->dropColumn('conversation_admin_id');
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
