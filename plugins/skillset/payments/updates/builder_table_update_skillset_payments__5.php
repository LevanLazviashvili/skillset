<?php namespace skillset\Payments\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetPayments5 extends Migration
{
    public function up()
    {
        Schema::table('skillset_payments_', function($table)
        {
            $table->string('transaction_id', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_payments_', function($table)
        {
            $table->dropColumn('transaction_id');
            $table->string('status', 255)->default('NULL')->change();
            $table->string('payment_hash', 255)->default('NULL')->change();
            $table->string('ipay_payment_id', 255)->default('NULL')->change();
            $table->string('status_description', 255)->default('NULL')->change();
            $table->string('payment_method', 255)->default('NULL')->change();
            $table->string('card_type', 255)->default('NULL')->change();
            $table->string('pan', 255)->default('NULL')->change();
            $table->string('pre_auth_status', 255)->default('NULL')->change();
            $table->string('capture_method', 255)->default('NULL')->change();
            $table->string('payment_order_id', 255)->default('NULL')->change();
            $table->string('ip', 191)->default('NULL')->change();
        });
    }
}
