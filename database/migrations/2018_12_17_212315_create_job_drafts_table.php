<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobDraftsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('job_drafts', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumInteger('user_id');
            $table->timestampTz('created_at')->default($this->db->raw('CURRENT_TIMESTAMP(0)'));
            $table->string('key');
            $table->text('value');

            $table->index('user_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('job_drafts');
    }
}
