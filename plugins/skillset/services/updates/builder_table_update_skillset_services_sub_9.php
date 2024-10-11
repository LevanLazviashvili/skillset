<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetServicesSub9 extends Migration
{
    public function up()
    {
        Schema::table('skillset_services_sub', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('slug', 255)->default('null')->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_services_sub', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->string('slug', 255)->default('NULL')->change();
        });
    }
}
