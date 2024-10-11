<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetDetailsLegalTypes extends Migration
{
    public function up()
    {
        Schema::table('skillset_details_legal_types', function($table)
        {
            $table->smallInteger('status_id')->default(0);
            $table->integer('sort_order')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_details_legal_types', function($table)
        {
            $table->dropColumn('status_id');
            $table->integer('sort_order')->default(NULL)->change();
        });
    }
}
