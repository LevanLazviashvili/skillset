<?php namespace skillset\Offers\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOffersWorkers6 extends Migration
{
    public function up()
    {
        Schema::table('skillset_offers_workers', function($table)
        {
            $table->dateTime('last_notified_at')->nullable();
            $table->smallInteger('seen')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_offers_workers', function($table)
        {
            $table->dropColumn('last_notified_at');
            $table->dropColumn('seen');
            $table->text('worker_response')->default('NULL')->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->integer('conversation_id')->default(NULL)->change();
        });
    }
}
