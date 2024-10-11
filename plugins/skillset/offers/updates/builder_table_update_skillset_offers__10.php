<?php namespace skillset\Offers\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOffers10 extends Migration
{
    public function up()
    {
        Schema::table('skillset_offers_', function($table)
        {
            $table->integer('service_id')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_offers_', function($table)
        {
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->text('offer')->default('NULL')->change();
            $table->string('title', 191)->default('NULL')->change();
            $table->integer('service_id')->nullable(false)->change();
            $table->string('search_params', 191)->default('\'null\'')->change();
        });
    }
}
