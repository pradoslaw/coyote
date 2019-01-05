<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTopicsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('topics', function (Blueprint $table) {
            $table->timestampTz('locked_at')->nullable();
            $table->integer('locker_id')->nullable();
            $table->timestampTz('moved_at')->nullable();
            $table->integer('mover_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('topics', function (Blueprint $table) {
            $table->dropColumn('locked_at', 'moved_at', 'locker_id', 'mover_id');
        });
    }
}
