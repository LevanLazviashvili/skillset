<?php namespace skillset\Log\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetLogSms extends Migration
{
    public function up()
    {
        Schema::create('skillset_log_sms', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('to_phone', 255);
            $table->string('sms_text', 255);
            $table->text('curl_response');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_log_sms');
    }
}
