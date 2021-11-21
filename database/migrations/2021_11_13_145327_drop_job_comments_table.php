<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropJobCommentsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->drop('job_comments');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->create('job_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('job_id');
            $table->integer('user_id')->nullable();
            $table->string('email')->nullable();
            $table->text('text');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('job_comments')->onDelete('cascade');

            $table->index('job_id');
        });
    }
}
