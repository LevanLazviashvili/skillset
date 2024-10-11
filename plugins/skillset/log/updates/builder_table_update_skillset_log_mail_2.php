<?php namespace skillset\Log\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetLogMail2 extends Migration
{
    public function up()
    {
        Schema::table('skillset_log_mail', function($table)
        {
            $table->dropColumn('curl_response');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_log_mail', function($table)
        {
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->time('curl_response');
        });
    }
}
