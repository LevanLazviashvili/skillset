<?php namespace skillset\Conversations\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetConversations2 extends Migration
{
    public function up()
    {
        Schema::table('skillset_conversations_', function($table)
        {
            $table->integer('created_by');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_conversations_', function($table)
        {
            $table->dropColumn('created_by');
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
