<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsCountries extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_countries', function($table)
        {
            $table->smallInteger('status_id');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_countries', function($table)
        {
            $table->dropColumn('status_id');
        });
    }
}
