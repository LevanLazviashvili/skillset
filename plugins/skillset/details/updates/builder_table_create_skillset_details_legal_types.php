<?php namespace skillset\details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetDetailsLegalTypes extends Migration
{
    public function up()
    {
        Schema::create('skillset_details_legal_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('id');
            $table->string('title');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_details_legal_types');
    }
}
