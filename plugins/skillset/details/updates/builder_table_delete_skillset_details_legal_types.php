<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteSkillsetDetailsLegalTypes extends Migration
{
    public function up()
    {
        Schema::dropIfExists('skillset_details_legal_types');
    }
    
    public function down()
    {
        Schema::create('skillset_details_legal_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('id');
            $table->string('title', 191);
        });
    }
}
