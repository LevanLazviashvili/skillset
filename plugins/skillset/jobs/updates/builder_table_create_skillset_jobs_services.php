<?php namespace skillset\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetJobsServices extends Migration
{
    public function up()
    {
        Schema::create('skillset_jobs_services', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('offer_id')->nullable()->unsigned();
            $table->integer('order_id')->nullable()->unsigned();
            $table->string('title')->nullable();
            $table->decimal('amount', 8, 2)->nullable();
            $table->integer('unit_id')->unsigned();
            $table->decimal('unit_price', 8, 2);
            $table->boolean('pre')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_jobs_services');
    }
}