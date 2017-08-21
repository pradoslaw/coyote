<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSeqToInvoicesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('invoices', function (Blueprint $table) {
            $table->smallInteger('seq')->nullable();
            $table->unique('number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('invoices', function (Blueprint $table) {
            $table->dropColumn(['seq']);
            $table->dropUnique('invoices_number_unique');
        });
    }
}
