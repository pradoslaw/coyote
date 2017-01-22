<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryToAlertTypesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('alert_types', function (Blueprint $table) {
            $table->addColumn('string', 'category', ['length' => 100])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('alert_types', function (Blueprint $table) {
            $table->dropColumn(['category']);
        });
    }
}
