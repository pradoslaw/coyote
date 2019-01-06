<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStreamsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('streams', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at')->useCurrent();
            $table->string('verb');
            $table->string('ip')->nullable();
            $table->string('browser', 1000)->nullable();
            $table->string('fingerprint')->nullable();
            $table->jsonb('actor')->nullable();
            $table->jsonb('object')->nullable();
            $table->jsonb('target')->nullable();
            $table->string('login')->nullable();
            $table->string('email')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('streams');
    }
}
