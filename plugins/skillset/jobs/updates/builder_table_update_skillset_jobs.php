<?php namespace skillset\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetJobs extends Migration
{
    public function up()
    {
        Schema::table('skillset_jobs', function($table)
        {
            $table->smallInteger('author_role')->unsigned()->default(1);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_jobs', function($table)
        {
            $table->dropColumn('author_role');
        });
    }
}