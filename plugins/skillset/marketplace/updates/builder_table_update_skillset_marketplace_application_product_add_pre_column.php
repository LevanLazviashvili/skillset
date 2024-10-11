<?php namespace skillset\Marketplace\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetMarketplaceApplicationProductAddPreColumn extends Migration
{
    public function up()
    {
        Schema::table('skillset_marketplace_application_product', function($table)
        {
            $table->boolean('pre')->default(1);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_marketplace_application_product', function($table)
        {
            $table->dropColumn('pre');
        });
    }
}