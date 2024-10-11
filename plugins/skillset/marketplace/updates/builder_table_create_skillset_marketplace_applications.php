<?php namespace skillset\Marketplace\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSkillsetMarketplaceApplications extends Migration
{
    public function up()
    {
        Schema::create('skillset_marketplace_applications', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->integer('quantity')->nullable()->unsigned()->default(0);
            $table->decimal('price', 10, 2)->unsigned()->nullable();
            $table->smallInteger('trade_type')->unsigned()->default(1);
            $table->smallInteger('type')->unsigned()->default(1);
            $table->smallInteger('category_id')->unsigned();
            $table->integer('region_id')->unsigned()->nullable();
            $table->string('country')->nullable();
            $table->boolean('active')->default(1);
            $table->smallInteger('status')->unsigned()->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('region_id')->references('id')->on('skillset_details_regions');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('skillset_marketplace_applications');
    }
}