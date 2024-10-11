<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotificationsTemplate2 extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_template', function($table)
        {
            $table->string('title', 191)->nullable()->change();
            $table->string('description', 191)->nullable()->change();
            $table->string('icon', 191)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_template', function($table)
        {
            $table->string('title', 191)->nullable(false)->change();
            $table->string('description', 191)->nullable(false)->change();
            $table->string('icon', 191)->nullable(false)->change();
        });
    }
}
