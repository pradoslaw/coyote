<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFirmIndustriesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('firm_industries', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('industry_id');
            $table->integer('firm_id');

            $table->index('firm_id');

            $table->foreign('industry_id')->references('id')->on('industries')->onDelete('cascade');
            $table->foreign('firm_id')->references('id')->on('firms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('firm_industries');
    }
}
