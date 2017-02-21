<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlansTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('plans', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->float('price')->default(0.0);
            $table->smallInteger('currency_id')->default(\Coyote\Currency::PLN);
            $table->smallInteger('is_active')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('plans');
    }
}
