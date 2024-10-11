<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetServicesSubToUser3 extends Migration
{
    public function up()
    {
        Schema::table('skillset_services_sub_to_user', function($table)
        {
            $table->integer('user_id');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_services_sub_to_user', function($table)
        {
            $table->dropColumn('user_id');
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
        });
    }
}
