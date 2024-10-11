<?php namespace skillset\Conversations\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetConversationsMessages extends Migration
{
    public function up()
    {
        Schema::table('skillset_conversations_messages', function($table)
        {
//            $table->smallInteger('notified')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_conversations_messages', function($table)
        {
            $table->dropColumn('notified');
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
