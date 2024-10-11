<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotifications8 extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_', function($table)
        {
            $table->integer('send_to_user_id')->default(0)->change();
            $table->dropColumn('icon_type');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_', function($table)
        {
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->integer('send_to_user_id')->default(NULL)->change();
            $table->smallInteger('icon_type')->nullable()->default(0);
        });
    }
}
