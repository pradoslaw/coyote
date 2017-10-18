<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropSessionPathIndexFromSessionsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->dropIndex('sessions_path_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('CREATE INDEX "sessions_path_index" ON "sessions" (path text_pattern_ops)');
    }
}
