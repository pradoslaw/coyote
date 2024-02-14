<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIndexToActivitiesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('activities', function (Blueprint $table) {
            $table->index([$this->db->raw('created_at DESC')],
                'activities_created_at desc_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('activities', function (Blueprint $table) {
            $table->dropIndex('activities_created_at desc_index');
        });
    }
}
