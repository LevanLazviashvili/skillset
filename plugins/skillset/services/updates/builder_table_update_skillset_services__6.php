<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetServices6 extends Migration
{
    public function up()
    {
        Schema::table('skillset_services_', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('parent_id')->default(0)->change();
            $table->integer('nest_left')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_services_', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->integer('parent_id')->default(NULL)->change();
            $table->integer('nest_left')->default(NULL)->change();
        });
    }
}
