<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsUnits5 extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_units', function($table)
        {
            $table->integer('sort_order')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_units', function($table)
        {
            $table->integer('sort_order')->default(1)->change();
        });
    }
}
