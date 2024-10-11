<?php namespace skillset\Rating\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetRating3 extends Migration
{
    public function up()
    {
        Schema::table('skillset_rating_', function($table)
        {
            $table->integer('rater_id');
            $table->integer('rated_id');
            $table->dropColumn('user_id');
            $table->dropColumn('worker_id');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_rating_', function($table)
        {
            $table->dropColumn('rater_id');
            $table->dropColumn('rated_id');
            $table->text('comment')->default('NULL')->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->integer('user_id');
            $table->integer('worker_id');
        });
    }
}
