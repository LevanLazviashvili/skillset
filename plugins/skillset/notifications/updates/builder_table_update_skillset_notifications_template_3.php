<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotificationsTemplate3 extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_template', function($table)
        {
            $table->string('title', 191)->default('null')->change();
            $table->string('description', 191)->default('null')->change();
            $table->dropColumn('icon');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_template', function($table)
        {
            $table->string('title', 191)->default('NULL')->change();
            $table->string('description', 191)->default('NULL')->change();
            $table->string('icon', 191)->nullable()->default('NULL');
        });
    }
}
