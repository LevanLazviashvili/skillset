<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsTexts extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_texts', function($table)
        {
            $table->dropColumn('videos');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_texts', function($table)
        {
            $table->text('videos')->nullable();
        });
    }
}
