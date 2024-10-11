<?php namespace skillset\Marketplace\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetMarketplaceOrders extends Migration
{
    public function up()
    {
        Schema::create('skillset_marketplace_orders', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('offer_id')->unsigned();
            $table->decimal('price')->unsigned()->default(0);
            $table->decimal('total_price')->unsigned()->default(0);
            $table->decimal('app_percent')->unsigned()->default(0);
            $table->decimal('app_percent_amount')->unsigned()->default(0);
            $table->decimal('bank_percent')->unsigned()->default(0);
            $table->decimal('bank_percent_amount')->unsigned()->default(0);
            $table->smallInteger('payment_type')->unsigned()->default(0);
            $table->string('payment_hash')->nullable();
            $table->string('payment_order_id')->nullable();
            $table->smallInteger('status')->unsigned()->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_marketplace_orders');
    }
}