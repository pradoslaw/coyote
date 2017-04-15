<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePredictionsInGuestsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('guests', function (Blueprint $table) {
            $table->jsonb('interests')->nullable();
            $table->dropColumn('predictions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('guests', function (Blueprint $table) {
            $table->dropColumn('interests');
            $table->jsonb('predictions')->nullable();
        });
    }
}
