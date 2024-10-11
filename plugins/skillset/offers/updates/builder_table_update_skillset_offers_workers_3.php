<?php namespace skillset\Offers\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOffersWorkers3 extends Migration
{
    public function up()
    {
        Schema::table('skillset_offers_workers', function($table)
        {
            $table->integer('conversation_id')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_offers_workers', function($table)
        {
            $table->dropColumn('conversation_id');
            $table->text('worker_response')->default('NULL')->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
