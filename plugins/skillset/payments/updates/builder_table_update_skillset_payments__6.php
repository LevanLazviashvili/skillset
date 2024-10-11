<?php namespace skillset\Payments\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetPayments6 extends Migration
{
    public function up()
    {
        Schema::table('skillset_payments_', function($table)
        {
            $table->integer('payment_type')->nullable()->default(0);
            $table->string('status', 255)->default('null')->change();
            $table->string('payment_hash', 255)->default('null')->change();
            $table->string('ipay_payment_id', 255)->default('null')->change();
            $table->string('status_description', 255)->default('null')->change();
            $table->string('payment_method', 255)->default('null')->change();
            $table->string('card_type', 255)->default('null')->change();
            $table->string('pan', 255)->default('null')->change();
            $table->string('pre_auth_status', 255)->default('null')->change();
            $table->string('capture_method', 255)->default('null')->change();
            $table->string('payment_order_id', 255)->default('null')->change();
            $table->string('ip', 191)->default('null')->change();
            $table->string('transaction_id', 255)->default('null')->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_payments_', function($table)
        {
            $table->dropColumn('payment_type');
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
            $table->string('transaction_id', 255)->default('NULL')->change();
        });
    }
}
