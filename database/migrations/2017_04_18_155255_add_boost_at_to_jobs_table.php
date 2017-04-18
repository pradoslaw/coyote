<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBoostAtToJobsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('jobs', function (Blueprint $table) {
            $table->timestampTz('boost_at')->nullable();
        });

        $this->db->table('jobs')->update(['boost_at' => new \Illuminate\Database\Query\Expression('created_at')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('jobs', function (Blueprint $table) {
            $table->dropColumn(['boost_at']);
        });
    }
}
