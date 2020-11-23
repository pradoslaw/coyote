<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserRelationsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('user_relations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('related_user_id');
            $table->timestampTz('created_at')->useCurrent();
            $table->boolean('is_blocked')->default(false);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('related_user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('user_relations');
    }
}
