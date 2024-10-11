<?php namespace skillset\Payments\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetPaymentsAddFinishedField extends Migration
{
    public function up()
    {
        Schema::table('skillset_payments_', function($table)
        {
            $table->boolean('finished')->nullable()->after('payment_type');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_payments_', function($table)
        {
            $table->dropColumn('finished');
        });
    }
}