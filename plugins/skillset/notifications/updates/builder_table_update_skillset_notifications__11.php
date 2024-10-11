<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotifications11 extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_', function($table)
        {
            $table->dateTime('last_send_date')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_', function($table)
        {
            $table->dropColumn('last_send_date');
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
