<?php namespace skillset\Offers\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOffersWorkers4 extends Migration
{
    public function up()
    {
        Schema::table('skillset_offers_workers', function($table)
        {
            $table->integer('notification_count')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_offers_workers', function($table)
        {
            $table->dropColumn('notification_count');
            $table->text('worker_response')->default('NULL')->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
