<?php namespace skillset\Orders\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetOrdersFillbalance extends Migration
{
    public function up()
    {
        Schema::create('skillset_orders_fillbalance', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->smallInteger('status_id')->nullable()->default(0);
            $table->integer('user_id');
            $table->integer('payment_hash')->nullable();
            $table->integer('payment_order_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_orders_fillbalance');
    }
}
