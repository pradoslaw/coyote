<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackagesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('packages', function (Blueprint $table) {
            $table->smallInteger('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->float('price')->default(0.0);
            $table->smallInteger('currency_id')->default(\Coyote\Currency::PLN);

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('packages');
    }
}
