<?php namespace skillset\Offers\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOffersWorkers2 extends Migration
{
    public function up()
    {
        Schema::table('skillset_offers_workers', function($table)
        {
            $table->date('end_date')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_offers_workers', function($table)
        {
            $table->dropColumn('end_date');
            $table->text('worker_response')->default('NULL')->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->dateTime('last_notified_at')->default('NULL')->change();
        });
    }
}
