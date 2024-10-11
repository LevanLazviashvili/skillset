<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetServices7 extends Migration
{
    public function up()
    {
        Schema::table('skillset_services_', function($table)
        {
            $table->string('slug', 255)->nullable();
            $table->integer('parent_id')->default(0)->change();
            $table->integer('nest_left')->default(0)->change();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_services_', function($table)
        {
            $table->dropColumn('slug');
            $table->integer('parent_id')->default(NULL)->change();
            $table->integer('nest_left')->default(NULL)->change();
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
        });
    }
}
