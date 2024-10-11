<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotificationsTemplate5 extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_template', function($table)
        {
            $table->string('button_title', 100)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_template', function($table)
        {
            $table->dropColumn('button_title');
            $table->string('title', 191)->default('\'null\'')->change();
            $table->string('description', 191)->default('\'null\'')->change();
        });
    }
}
