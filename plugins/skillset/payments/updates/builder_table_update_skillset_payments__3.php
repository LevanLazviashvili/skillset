<?php namespace skillset\Payments\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetPayments3 extends Migration
{
    public function up()
    {
        Schema::table('skillset_payments_', function($table)
        {
            $table->dropColumn('insert_date');
        });
    }
    
    public function down()
    {
        Schema::table('skillset_payments_', function($table)
        {
            $table->dateTime('insert_date')->nullable();
        });
    }
}
