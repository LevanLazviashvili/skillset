<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotificationsTemplate extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_template', function($table)
        {
            $table->string('icon');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_template', function($table)
        {
            $table->dropColumn('icon');
        });
    }
}
