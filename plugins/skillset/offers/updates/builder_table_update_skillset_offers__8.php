<?php namespace skillset\Offers\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOffers8 extends Migration
{
    public function up()
    {
        Schema::table('skillset_offers_', function($table)
        {
            $table->integer('service_id')->default(0);
            $table->string('search_params')->nullable();
            $table->text('offer')->default('null')->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_offers_', function($table)
        {
            $table->dropColumn('service_id');
            $table->dropColumn('search_params');
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->text('offer')->default('NULL')->change();
        });
    }
}
