<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsRegions4 extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_regions', function($table)
        {
            $table->string('title', 255)->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_regions', function($table)
        {
            $table->integer('title')->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
}
