<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('payments', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestampsTz();
            $table->integer('job_id');
            $table->smallInteger('plan_id');
            $table->smallInteger('status_id')->default(\Coyote\Payment::NEW);
            $table->smallInteger('days');
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();
            $table->integer('invoice_id')->nullable();

            $table->primary('id');
            $table->index('job_id');

            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('no action');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('no action');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('payments');
    }
}
