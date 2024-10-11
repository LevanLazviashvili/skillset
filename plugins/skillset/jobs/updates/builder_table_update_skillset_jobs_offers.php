<?php namespace skillset\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetJobsOffers extends Migration
{
    public function up()
    {
        Schema::table('skillset_jobs_offers', function($table)
        {
            $table->smallInteger('payment_type')->unsigned()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_jobs_offers', function($table)
        {
            $table->dropColumn('payment_type');
        });
    }
}
