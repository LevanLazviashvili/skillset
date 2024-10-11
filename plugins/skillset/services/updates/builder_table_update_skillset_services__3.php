<?php namespace skillset\Services\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetServices3 extends Migration
{
    public function up()
    {
        Schema::table('skillset_services_', function($table)
        {
            $table->integer('order_id')->default(0)->change();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_services_', function($table)
        {
            $table->integer('order_id')->default(null)->change();
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
        });
    }
}
