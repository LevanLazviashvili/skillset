<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOrders9 extends Migration
{
    public function up()
    {
        Schema::table('skillset_orders_', function($table)
        {
            $table->string('custom_client_address', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_orders_', function($table)
        {
            $table->dropColumn('custom_client_address');
            $table->dateTime('start_date')->default('NULL')->change();
            $table->date('end_date')->default('NULL')->change();
            $table->smallInteger('rate')->default(NULL)->change();
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->text('description')->default('\'null\'')->change();
            $table->text('feedback')->default('NULL')->change();
            $table->string('payment_hash', 255)->default('NULL')->change();
            $table->string('payment_order_id', 255)->default('NULL')->change();
            $table->dateTime('ended_at')->default('NULL')->change();
            $table->text('comment')->default('NULL')->change();
            $table->string('custom_client_phone', 191)->default('NULL')->change();
        });
    }
}
