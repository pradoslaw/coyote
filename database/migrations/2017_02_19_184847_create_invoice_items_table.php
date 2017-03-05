<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceItemsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('invoice_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('invoice_id');
            $table->string('description');
            $table->float('price');
            $table->smallInteger('currency_id')->default(\Coyote\Currency::PLN);
            $table->float('vat_rate')->default(1.23);

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('no action');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('invoice_items');
    }
}
