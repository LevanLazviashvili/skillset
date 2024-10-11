<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetServicesSub7 extends Migration
{
    public function up()
    {
        Schema::table('skillset_services_sub', function($table)
        {
            $table->string('slug', 255)->nullable();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_services_sub', function($table)
        {
            $table->dropColumn('slug');
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
        });
    }
}
