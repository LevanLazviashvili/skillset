<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteSkillsetServicesSub extends Migration
{
    public function up()
    {
        Schema::dropIfExists('skillset_services_sub');
    }
    
    public function down()
    {
        Schema::create('skillset_services_sub', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('service_id');
            $table->integer('title');
            $table->smallInteger('default');
            $table->integer('sort_order');
            $table->integer('user_id');
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
        });
    }
}
