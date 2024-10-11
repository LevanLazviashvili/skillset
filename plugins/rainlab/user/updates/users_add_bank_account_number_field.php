<?php
namespace RainLab\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UsersAddBankAccountNumberField extends Migration
{

    public function up()
    {
        Schema::table('users', function ($table) {
            $table->string('bank_account_number')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('bank_account_number');
        });
    }
}
