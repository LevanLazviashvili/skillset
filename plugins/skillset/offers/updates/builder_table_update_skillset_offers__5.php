<?php namespace skillset\Offers\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOffers5 extends Migration
{
    public function up()
    {
        Schema::table('skillset_offers_', function($table)
        {
            $table->integer('service_id');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_offers_', function($table)
        {
            $table->dropColumn('service_id');
        });
    }
}
