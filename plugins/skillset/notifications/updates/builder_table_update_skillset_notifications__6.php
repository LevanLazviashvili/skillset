<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotifications6 extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_', function($table)
        {
            $table->integer('send_to_user_id')->nullable();
            $table->smallInteger('seen')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_', function($table)
        {
            $table->dropColumn('send_to_user_id');
            $table->dropColumn('seen');
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
