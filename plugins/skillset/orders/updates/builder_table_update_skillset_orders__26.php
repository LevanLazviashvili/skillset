<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOrders26 extends Migration
{
    public function up()
    {
        Schema::table('skillset_orders_', function($table)
        {
            $table->smallInteger('end_date_notified')->default(0);
            $table->date('end_date')->nullable()->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_orders_', function($table)
        {
            $table->dropColumn('end_date_notified');
            $table->dateTime('start_date')->default('NULL')->change();
            $table->dateTime('end_date')->nullable()->unsigned(false)->default('NULL')->change();
            $table->smallInteger('rate')->default(NULL)->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->text('description')->default('NULL')->change();
            $table->text('feedback')->default('NULL')->change();
            $table->string('payment_hash', 255)->default('NULL')->change();
            $table->string('payment_order_id', 255)->default('NULL')->change();
            $table->dateTime('ended_at')->default('NULL')->change();
        });
    }
}
