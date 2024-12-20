<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetServicesSub extends Migration
{
    public function up()
    {
        Schema::create('skillset_services_sub', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('service_id');
            $table->integer('title');
            $table->smallInteger('default');
            $table->integer('sort_order');
            $table->integer('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_services_sub');
    }
}
