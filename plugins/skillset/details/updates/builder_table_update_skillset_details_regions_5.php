<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsRegions5 extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_regions', function($table)
        {
            $table->smallInteger('status_id')->nullable(false)->unsigned(false)->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_regions', function($table)
        {
            $table->integer('status_id')->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
}
