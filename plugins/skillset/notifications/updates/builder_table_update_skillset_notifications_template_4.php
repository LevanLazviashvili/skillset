<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotificationsTemplate4 extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_template', function($table)
        {
            $table->smallInteger('icon_type')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_template', function($table)
        {
            $table->dropColumn('icon_type');
            $table->string('title', 191)->default('\'null\'')->change();
            $table->string('description', 191)->default('\'null\'')->change();
        });
    }
}
