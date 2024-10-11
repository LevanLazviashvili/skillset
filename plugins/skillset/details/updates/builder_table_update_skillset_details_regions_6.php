<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsRegions6 extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_regions', function($table)
        {
            $table->integer('sort_order')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_regions', function($table)
        {
            $table->integer('sort_order')->nullable(false)->change();
        });
    }
}
