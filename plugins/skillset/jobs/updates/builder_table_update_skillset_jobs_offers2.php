<?php namespace skillset\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetJobsOffers2 extends Migration
{
    public function up()
    {
        Schema::table('skillset_jobs_offers', function($table)
        {
            $table->renameColumn('worker_id', 'author_id');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_jobs_offers', function($table)
        {
            $table->renameColumn('author_id', 'worker_id');
        });
    }
}