<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOrders extends Migration
{
    public function up()
    {
        Schema::table('skillset_orders_', function($table)
        {
            $table->smallInteger('seen')->default(0);
        });
    }
}
