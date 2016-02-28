<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFirewallTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('firewall', function (Blueprint $table) {
            $table->smallInteger('id', true);
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'))->nullable();
            $table->timestampTz('updated_at')->nullable();
            $table->timestampTz('expire_at')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('ip', 100)->nullable();
            $table->string('email')->nullable();
            $table->text('reason')->nullable();
            $table->integer('moderator_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('firewall');
    }
}
