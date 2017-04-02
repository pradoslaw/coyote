<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSessionsTable extends Migration
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
            $table->string('path', 1000)->nullable();
            $table->dropUnique('sessions_id_unique');

            $table->dropColumn(['ip', 'created_at', 'updated_at', 'url', 'browser', 'payload']);
        });

        $this->db->unprepared('CREATE INDEX "sessions_path_index" ON "sessions" USING btree (LOWER(path))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->dropIndex('sessions_path_index');

            $table->string('ip', 45)->nullable();
            $table->text('payload')->nullable();
            $table->dateTimeTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->dateTimeTz('updated_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->string('url', 4000)->nullable();
            $table->string('browser', 1000)->nullable();

            $table->dropColumn('path');
            $table->unique('id');
        });
    }
}
