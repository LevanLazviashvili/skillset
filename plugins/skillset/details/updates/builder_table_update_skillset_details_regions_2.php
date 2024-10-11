<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsRegions2 extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_regions', function($table)
        {
            $table->integer('sort_order');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_regions', function($table)
        {
            $table->dropColumn('sort_order');
        });
    }
}
