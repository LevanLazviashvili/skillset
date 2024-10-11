<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetServicesToUser extends Migration
{
    public function up()
    {
        Schema::create('skillset_services_to_user', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id');
            $table->integer('service_id');
            $table->text('description');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_services_to_user');
    }
}
