<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsInstructions3 extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_instructions', function($table)
        {
            $table->smallInteger('status_id')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_instructions', function($table)
        {
            $table->dropColumn('status_id');
        });
    }
}
