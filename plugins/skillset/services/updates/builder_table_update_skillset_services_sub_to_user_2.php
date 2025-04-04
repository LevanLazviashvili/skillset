<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetServicesSubToUser2 extends Migration
{
    public function up()
    {
        Schema::table('skillset_services_sub_to_user', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->smallInteger('status_id')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_services_sub_to_user', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('status_id');
        });
    }
}
