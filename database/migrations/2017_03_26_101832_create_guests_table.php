<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuestsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('guests', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('user_id')->nullable();
            $table->timestampsTz();
            $table->jsonb('predictions')->nullable();

            $table->primary('id');
            $table->unique('user_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('guests');
    }
}
