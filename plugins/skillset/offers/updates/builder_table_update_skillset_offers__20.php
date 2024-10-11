<?php namespace skillset\Offers\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetOffers20 extends Migration
{
    public function up()
    {
        Schema::table('skillset_offers_', function($table)
        {
            $table->text('comment')->nullable()->change();
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
            $table->string('search_params', 191)->default('\'null\'')->change();
            $table->text('worker_response')->default('NULL')->change();
            $table->string('custom_client_phone', 255)->default('NULL')->change();
            $table->string('custom_client_address', 191)->default('NULL')->change();
            $table->text('comment')->nullable(false)->change();
        });
    }
}
