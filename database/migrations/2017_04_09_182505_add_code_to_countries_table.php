<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodeToCountriesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('countries', function (Blueprint $table) {
            $table->string('code')->nullable();
            $table->float('vat_rate')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('countries', function (Blueprint $table) {
            $table->dropColumn(['code', 'vat_rate']);
        });
    }
}
