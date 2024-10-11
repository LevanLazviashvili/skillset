<?php namespace skillset\Conversations\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetConversations4 extends Migration
{
    public function up()
    {
        Schema::table('skillset_conversations_', function($table)
        {
            $table->integer('conversation_admin_id')->nullable()->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_conversations_', function($table)
        {
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->integer('conversation_admin_id')->nullable(false)->default(null)->change();
        });
    }
}
