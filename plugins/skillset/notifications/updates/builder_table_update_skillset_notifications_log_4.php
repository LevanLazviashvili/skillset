<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotificationsLog4 extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_log', function($table)
        {
            $table->string('title', 255)->nullable();
            $table->text('body');
            $table->text('user_ids')->default('null')->change();
            $table->dropColumn('template_id');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_log', function($table)
        {
            $table->dropColumn('title');
            $table->dropColumn('body');
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->text('user_ids')->default('NULL')->change();
            $table->integer('template_id');
        });
    }
}
