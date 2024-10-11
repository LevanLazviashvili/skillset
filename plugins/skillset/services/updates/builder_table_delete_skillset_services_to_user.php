<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteSkillsetServicesToUser extends Migration
{
    public function up()
    {
        Schema::dropIfExists('skillset_services_to_user');
    }
    
    public function down()
    {
        Schema::create('skillset_services_to_user', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id');
            $table->integer('service_id');
        });
    }
}
