<?php namespace skillset\Log\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetLogMail extends Migration
{
    public function up()
    {
        Schema::create('skillset_log_mail', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('to_mail');
            $table->text('text');
            $table->time('curl_response');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_log_mail');
    }
}
