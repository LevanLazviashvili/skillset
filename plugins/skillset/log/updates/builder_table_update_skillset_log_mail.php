<?php namespace skillset\Log\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetLogMail extends Migration
{
    public function up()
    {
        Schema::table('skillset_log_mail', function($table)
        {
            $table->string('to_mail', 255)->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_log_mail', function($table)
        {
            $table->integer('to_mail')->nullable(false)->unsigned(false)->default(null)->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
