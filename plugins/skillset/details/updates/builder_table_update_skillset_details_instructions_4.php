<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsInstructions4 extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_instructions', function($table)
        {
            $table->string('thumb', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_instructions', function($table)
        {
            $table->dropColumn('thumb');
        });
    }
}
