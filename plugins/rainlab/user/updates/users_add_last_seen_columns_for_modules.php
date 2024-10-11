<?php
namespace RainLab\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UsersAddLastSeenColumnsForModules extends Migration
{

    public function up()
    {
        Schema::table('users', function ($table) {
            $table->dateTime('last_seen_jobs')->nullable();
            $table->dateTime('last_seen_marketplace')->nullable();
            $table->dateTime('last_seen_forum')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn(['last_seen_jobs', 'last_seen_marketplace', 'last_seen_forum']);
        });
    }
}
