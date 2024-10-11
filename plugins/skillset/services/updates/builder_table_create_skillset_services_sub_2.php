<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetServicesSub2 extends Migration
{
    public function up()
    {
        Schema::create('skillset_services_sub', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('service_id');
            $table->string('title');
            $table->smallInteger('default');
            $table->integer('sort_order')->default(0);
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
