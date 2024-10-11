<?php namespace skillset\Rating\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetRating4 extends Migration
{
    public function up()
    {
        Schema::table('skillset_rating_', function($table)
        {
            $table->smallInteger('status_id')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_rating_', function($table)
        {
            $table->dropColumn('status_id');
            $table->text('comment')->default('NULL')->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
