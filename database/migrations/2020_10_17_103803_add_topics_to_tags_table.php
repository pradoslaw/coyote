<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTopicsToTagsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('tags', function (Blueprint $table) {
            $table->integer('topics')->default(0);
        });

        $tags = \Coyote\Tag::all()->pluck('id')->toArray();

        $repository = app(\Coyote\Repositories\Contracts\TagRepositoryInterface::class);
        $repository->countTopics($tags);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('tags', function (Blueprint $table) {
            $table->dropColumn('topics');
        });
    }
}
