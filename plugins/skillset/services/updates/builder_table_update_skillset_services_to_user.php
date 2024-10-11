<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetServicesToUser extends Migration
{
    public function up()
    {
        Schema::table('skillset_services_to_user', function($table)
        {
            $table->dropColumn('description');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_services_to_user', function($table)
        {
            $table->text('description');
        });
    }
}
