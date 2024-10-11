<?php namespace skillset\Rating\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetRating2 extends Migration
{
    public function up()
    {
        Schema::table('skillset_rating_', function($table)
        {
            $table->integer('order_id');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_rating_', function($table)
        {
            $table->dropColumn('order_id');
            $table->text('comment')->default('NULL')->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
