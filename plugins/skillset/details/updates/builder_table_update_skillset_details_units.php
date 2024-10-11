<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsUnits extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_units', function($table)
        {
            $table->integer('status_id');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_units', function($table)
        {
            $table->dropColumn('status_id');
        });
    }
}
