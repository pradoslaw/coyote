<?php

use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStreamsCollection extends Migration
{
    /**
     * The name of the database connection to use.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->create('streams', function (Blueprint $collection) {
            $collection->background('actor.id');
            $collection->background('ip');
            $collection->background(['object.objectType', 'object.id', 'verb']);
            $collection->background(['object.objectType', 'object.id']);
            $collection->background(['target.objectType', 'target.id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->drop('streams');
    }
}
