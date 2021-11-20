<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        $comments = $this->db->table('job_comments')->orderBy('id')->get();

        foreach ($comments as $comment) {
            if (!$comment->user_id) {
                continue;
            }

            $data = array_except((array) $comment, ['job_id']);

            $this->db->table('comments')->insert(array_merge($data, [
                'resource_id' => $comment->job_id,
                'resource_type' => \Coyote\Job::class
            ]));
        }

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
