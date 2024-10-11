<?php namespace skillset\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetJobsOrders2 extends Migration
{
    public function up()
    {
        Schema::table('skillset_jobs_orders', function($table)
        {
            $table->boolean('end_date_notified')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_jobs_orders', function($table)
        {
            $table->dropColumn('end_date_notified');
        });
    }
}