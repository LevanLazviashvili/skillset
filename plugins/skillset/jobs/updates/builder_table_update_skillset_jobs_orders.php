<?php namespace skillset\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetJobsOrders extends Migration
{
    public function up()
    {
        Schema::table('skillset_jobs_orders', function($table)
        {
            $table->smallInteger('rated')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_jobs_orders', function($table)
        {
            $table->dropColumn('rated');
        });
    }
}