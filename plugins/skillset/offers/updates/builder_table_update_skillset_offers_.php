<?php namespace skillset\Offers\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOffers extends Migration
{
    public function up()
    {
        Schema::table('skillset_offers_', function($table)
        {
            $table->smallInteger('seen')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('skillset_offers_', function($table)
        {
            $table->dropColumn('seen');
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->text('offer')->default('NULL')->change();
            $table->string('title', 191)->default('NULL')->change();
            $table->string('search_params', 191)->default('\'null\'')->change();
        });
    }
}
