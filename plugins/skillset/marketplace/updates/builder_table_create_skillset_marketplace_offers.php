<?php namespace skillset\Marketplace\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetMarketplaceOffers extends Migration
{
    public function up()
    {
        Schema::create('skillset_marketplace_offers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('application_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('conversation_id')->nullable();
            $table->smallInteger('status')->unsigned()->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_marketplace_offers');
    }
}