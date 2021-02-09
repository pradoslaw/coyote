<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_resources', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tag_id');
            $table->morphs('resource');
            $table->smallInteger('priority')->nullable();
            $table->smallInteger('order')->nullable();

            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->index('tag_id');
            $table->index(['resource_type', 'tag_id']);
        });

        \Coyote\User\Skill::chunkById(10000, function ($result) {
            foreach ($result as $row) {
                \Coyote\Tag\Resource::forceCreate([
                    'tag_id'        => $row->tag_id,
                    'priority'      => $row->priority,
                    'order'         => $row->order,
                    'resource_id'   => $row->user_id,
                    'resource_type' => \Coyote\User::class
                ]);
            }
        });

        \Coyote\Job\Tag::chunkById(10000, function ($result) {
            foreach ($result as $row) {
                \Coyote\Tag\Resource::forceCreate([
                    'tag_id'        => $row->tag_id,
                    'priority'      => $row->priority,
                    'order'         => $row->order,
                    'resource_id'   => $row->job_id,
                    'resource_type' => \Coyote\Job::class
                ]);
            }
        });

        \Coyote\Microblog\Tag::chunkById(10000, function ($result) {
            foreach ($result as $row) {
                \Coyote\Tag\Resource::forceCreate([
                    'tag_id'        => $row->tag_id,
                    'resource_id'   => $row->microblog_id,
                    'resource_type' => \Coyote\Microblog::class
                ]);
            }
        });

        \Coyote\Topic\Tag::chunkById(10000, function ($result) {
            foreach ($result as $row) {
                \Coyote\Tag\Resource::forceCreate([
                    'tag_id'        => $row->tag_id,
                    'resource_id'   => $row->topic_id,
                    'resource_type' => \Coyote\Topic::class
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tag_resources');
    }
}
