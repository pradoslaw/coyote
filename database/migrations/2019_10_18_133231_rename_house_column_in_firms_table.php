<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameHouseColumnInFirmsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('firms', function (Blueprint $table) {
            $table->renameColumn('house', 'street_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('firms', function (Blueprint $table) {
            $table->renameColumn('street_number', 'house');
        });
    }
}
