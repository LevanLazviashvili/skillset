<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOrdersServices2 extends Migration
{
    public function up()
    {
        Schema::table('skillset_orders_services', function($table)
        {
            $table->smallInteger('editable')->nullable()->default(0);
            $table->integer('offer_id');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_orders_services', function($table)
        {
            $table->dropColumn('editable');
            $table->dropColumn('offer_id');
        });
    }
}
