<?php namespace skillset\Marketplace\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetMarketplaceOrders extends Migration
{
    public function up()
    {
        Schema::table('skillset_marketplace_orders', function($table)
        {
            $table->smallInteger('rated')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_marketplace_orders', function($table)
        {
            $table->dropColumn('rated');
        });
    }
}