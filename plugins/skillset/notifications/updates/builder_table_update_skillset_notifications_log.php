<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotificationsLog extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_log', function($table)
        {
            $table->string('device_token', 255);
            $table->dropColumn('user_id');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_log', function($table)
        {
            $table->dropColumn('device_token');
            $table->string('additional_data', 255)->default('NULL')->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->integer('user_id');
        });
    }
}
