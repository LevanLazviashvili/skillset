<?php namespace skillset\Rating\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetRating extends Migration
{
    public function up()
    {
        Schema::create('skillset_rating_', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id');
            $table->smallInteger('rate');
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_rating_');
    }
}
