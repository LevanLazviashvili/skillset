<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsInstructions2 extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_instructions', function($table)
        {
            $table->integer('sort_order')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_instructions', function($table)
        {
            $table->dropColumn('sort_order');
        });
    }
}
