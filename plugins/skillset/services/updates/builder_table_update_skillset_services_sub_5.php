<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetServicesSub5 extends Migration
{
    public function up()
    {
        Schema::table('skillset_services_sub', function($table)
        {
            $table->smallInteger('status_id')->default(1);
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_services_sub', function($table)
        {
            $table->dropColumn('status_id');
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
        });
    }
}
