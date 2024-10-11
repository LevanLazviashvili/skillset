<?php namespace skillset\Marketplace\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetMarketplaceOrdersAddCompletedAt extends Migration
{
    public function up()
    {
        Schema::table('skillset_marketplace_orders', function($table)
        {
            $table->dateTime('completed_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_marketplace_orders', function($table)
        {
            $table->dropColumn('completed_at');
        });
    }
}