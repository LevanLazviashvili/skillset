<?php namespace rainlab\user\updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class usersaddturnoffnotifications extends Migration
{
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->boolean('turn_off_notifications')->default(false);
        });
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'turn_off_notifications')) {
            Schema::table('users', function($table)
            {
                $table->dropColumn('turn_off_notifications');
            });
        }
    }
}
