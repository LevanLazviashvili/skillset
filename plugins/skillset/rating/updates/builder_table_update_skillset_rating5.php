<?php namespace skillset\Rating\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetRating5 extends Migration
{
    public function up()
    {
        Schema::table('skillset_rating_', function($table)
        {
            $table->smallInteger('order_type')->default(1);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_rating_', function($table)
        {
            $table->dropColumn('order_type');
        });
    }
}