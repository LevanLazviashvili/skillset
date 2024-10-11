<?php namespace skillset\Offers\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetOffersWorkers extends Migration
{
    public function up()
    {
        Schema::create('skillset_offers_workers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('offer_id');
            $table->integer('worker_id');
            $table->integer('status_id');
            $table->integer('worker_response');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_offers_workers');
    }
}
