<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOrdersServices extends Migration
{
    public function up()
    {
        Schema::table('skillset_orders_services', function($table)
        {
            $table->decimal('amount', 10, 2)->nullable(false)->unsigned(false)->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_orders_services', function($table)
        {
            $table->integer('amount')->nullable(false)->unsigned(false)->default(0)->change();
        });
    }
}
