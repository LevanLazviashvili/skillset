<?php namespace skillset\Payments\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSkillsetPayments4 extends Migration
{
    public function up()
    {
        Schema::table('skillset_payments_', function($table)
        {
            $table->integer('user_id');
            $table->decimal('price', 10, 0);
            $table->string('ip')->nullable();
            $table->integer('order_id')->nullable(false)->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('skillset_payments_', function($table)
        {
            $table->dropColumn('user_id');
            $table->dropColumn('price');
            $table->dropColumn('ip');
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
            $table->string('order_id', 255)->nullable()->unsigned(false)->default('NULL')->change();
        });
    }
}
