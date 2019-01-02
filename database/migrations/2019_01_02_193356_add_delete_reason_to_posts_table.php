<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeleteReasonToPostsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('posts', function (Blueprint $table) {
            $table->integer('deleter_id')->nullable();
            $table->string('delete_reason')->nullable();

            $table->foreign('deleter_id')->references('id')->on('users')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('posts', function (Blueprint $table) {
            $table->dropColumn(['deleter_id', 'delete_reason']);
        });
    }
}
