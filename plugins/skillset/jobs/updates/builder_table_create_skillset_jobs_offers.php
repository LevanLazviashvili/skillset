<?php namespace skillset\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetJobsOffers extends Migration
{
    public function up()
    {
        Schema::create('skillset_jobs_offers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('job_id')->unsigned();
            $table->integer('worker_id')->unsigned();
            $table->integer('conversation_id')->nullable();
            $table->date('estimated_completed_date')->nullable();
            $table->smallInteger('status')->unsigned()->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_jobs_offers');
    }
}