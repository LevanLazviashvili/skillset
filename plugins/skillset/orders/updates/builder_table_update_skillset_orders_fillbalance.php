<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOrdersFillbalance extends Migration
{
    public function up()
    {
        Schema::table('skillset_orders_fillbalance', function($table)
        {
            $table->string('payment_hash', 255)->nullable()->unsigned(false)->default(null)->change();
            $table->string('payment_order_id', 255)->nullable()->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_orders_fillbalance', function($table)
        {
            $table->integer('payment_hash')->nullable()->unsigned(false)->default(NULL)->change();
            $table->integer('payment_order_id')->nullable()->unsigned(false)->default(NULL)->change();
            $table->decimal('amount', 10, 2)->default(NULL)->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
        });
    }
}
