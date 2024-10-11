<?php namespace skillset\Rating\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetRating extends Migration
{
    public function up()
    {
        Schema::table('skillset_rating_', function($table)
        {
            $table->integer('worker_id');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_rating_', function($table)
        {
            $table->dropColumn('worker_id');
            $table->text('comment')->default('NULL')->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
