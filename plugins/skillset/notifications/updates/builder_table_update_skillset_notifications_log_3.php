<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotificationsLog3 extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_log', function($table)
        {
            $table->text('user_ids')->nullable();
            $table->dropColumn('seen');
            $table->dropColumn('additional_data');
            $table->dropColumn('device_token');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_log', function($table)
        {
            $table->string('additional_data', 255)->default('NULL')->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->string('device_token', 255)->nullable()->default('NULL');
        });
    }
}
