<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotifications10 extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_', function($table)
        {
            $table->smallInteger('status_id')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_', function($table)
        {
            $table->dropColumn('status_id');
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
