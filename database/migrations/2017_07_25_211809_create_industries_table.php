<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndustriesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('industries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('industries');
    }
}
