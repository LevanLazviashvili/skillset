<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetServicesSubToUser extends Migration
{
    public function up()
    {
        Schema::table('skillset_services_sub_to_user', function($table)
        {
            $table->integer('service_id')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_services_sub_to_user', function($table)
        {
            $table->dropColumn('service_id');
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
