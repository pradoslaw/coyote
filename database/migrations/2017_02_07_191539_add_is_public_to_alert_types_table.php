<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsPublicToAlertTypesTable extends Migration
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
            $table->addColumn('smallInteger', 'is_public')->default(1);
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
            $table->dropColumn(['is_public']);
        });
    }
}
