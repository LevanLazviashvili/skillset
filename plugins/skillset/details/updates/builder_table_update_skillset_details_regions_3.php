<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsRegions3 extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_regions', function($table)
        {
            $table->integer('sort_order')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_regions', function($table)
        {
            $table->integer('sort_order')->default(null)->change();
        });
    }
}
