<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('activities', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at')->useCurrent();
            $table->smallInteger('forum_id');
            $table->integer('topic_id');
            $table->integer('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->morphs('content');
            $table->string('excerpt')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('forum_id')->references('id')->on('forums')->onDelete('cascade');
            $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');

            $table->index('forum_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('activities');
    }
}
