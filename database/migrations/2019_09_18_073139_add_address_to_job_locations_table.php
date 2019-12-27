<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddressToJobLocationsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('job_locations', function (Blueprint $table) {
            $table->string('street')->nullable();
            $table->string('street_number', 50)->nullable();
            $table->smallInteger('country_id')->nullable();

            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('job_locations', function (Blueprint $table) {
            $table->dropColumn(['street', 'street_number', 'country_id']);
        });
    }
}
