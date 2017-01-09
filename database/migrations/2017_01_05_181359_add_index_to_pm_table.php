<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToPmTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('pm', function (Blueprint $table) {
            $table->index('author_id');
            $table->dropIndex('pm_root_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('pm', function (Blueprint $table) {
            $table->dropIndex('pm_author_id_index');
            $table->index('root_id');
        });
    }
}
