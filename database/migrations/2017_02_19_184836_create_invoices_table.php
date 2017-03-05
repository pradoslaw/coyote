<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->timestampTz('created_at');
            $table->string('name')->nullable();
            $table->string('number', 50)->nullable();
            $table->string('vat_id', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 30)->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('invoices');
    }
}
