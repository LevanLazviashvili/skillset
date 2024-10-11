<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOrders11 extends Migration
{
    public function up()
    {
        Schema::table('skillset_orders_', function($table)
        {
            $table->text('feedback')->nullable();
            $table->string('title', 191)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_orders_', function($table)
        {
            $table->dropColumn('feedback');
            $table->string('title', 191)->nullable(false)->change();
        });
    }
}
