<?php namespace skillset\Offers\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOffers17 extends Migration
{
    public function up()
    {
        Schema::table('skillset_offers_', function($table)
        {
            $table->string('custom_client_address')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_offers_', function($table)
        {
            $table->dropColumn('custom_client_address');
            $table->timestamp('created_at')->default('NULL')->change();
            $table->timestamp('updated_at')->default('NULL')->change();
            $table->text('offer')->default('NULL')->change();
            $table->string('title', 191)->default('NULL')->change();
            $table->string('search_params', 191)->default('\'null\'')->change();
            $table->text('worker_response')->default('NULL')->change();
            $table->string('custom_client_phone', 255)->default('NULL')->change();
        });
    }
}
