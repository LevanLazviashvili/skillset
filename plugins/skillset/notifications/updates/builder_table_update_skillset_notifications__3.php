<?php namespace skillset\Notifications\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetNotifications3 extends Migration
{
    public function up()
    {
        Schema::table('skillset_notifications_', function($table)
        {
            $table->smallInteger('send_to')->nullable()->default(0);
            $table->integer('send_to_user_id')->nullable();
            $table->dropColumn('params');
            $table->dropColumn('action');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_notifications_', function($table)
        {
            $table->dropColumn('send_to');
            $table->dropColumn('send_to_user_id');
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->string('params', 191);
            $table->string('action', 191);
        });
    }
}
