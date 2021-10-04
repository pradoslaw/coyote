<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnsInNotificationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_types', function (Blueprint $table) {
            $table->dropColumn(['profile', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_types', function (Blueprint $table) {
            $table->smallInteger('profile')->default(1);
            $table->smallInteger('email')->default(1);
        });
    }
}
