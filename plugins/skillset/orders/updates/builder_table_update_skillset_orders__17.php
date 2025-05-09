<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOrders17 extends Migration
{
    public function up()
    {
        Schema::table('skillset_orders_', function($table)
        {
            $table->decimal('bank_percent', 10, 2)->nullable()->default(0.00);
            $table->decimal('bank_percent_amount', 10, 2)->nullable()->default(0.00);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_orders_', function($table)
        {
            $table->dropColumn('bank_percent');
            $table->dropColumn('bank_percent_amount');
            $table->dateTime('start_date')->default('NULL')->change();
            $table->dateTime('end_date')->default('NULL')->change();
            $table->smallInteger('rate')->default(NULL)->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->decimal('price', 10, 0)->change();
            $table->text('description')->default('\'null\'')->change();
            $table->text('feedback')->default('NULL')->change();
            $table->string('payment_hash', 255)->default('NULL')->change();
            $table->string('payment_order_id', 255)->default('NULL')->change();
        });
    }
}
