<?php namespace skillset\Configuration\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetConfigurationRateCommission extends Migration
{
    public function up()
    {
        Schema::table('skillset_configuration_rate_commission', function($table)
        {
            $table->decimal('rate', 10, 2)->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_configuration_rate_commission', function($table)
        {
            $table->integer('rate')->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
}
