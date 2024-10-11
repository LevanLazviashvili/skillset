<?php namespace skillset\Offers\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOffersWorkers5 extends Migration
{
    public function up()
    {
        Schema::table('skillset_offers_workers', function($table)
        {
            $table->text('worker_response')->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_offers_workers', function($table)
        {
            $table->text('worker_response')->default('NULL')->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->integer('conversation_id')->default(NULL)->change();
        });
    }
}
