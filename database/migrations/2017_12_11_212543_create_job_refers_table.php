<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobRefersTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('job_refers', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('job_id');
            $table->uuid('guest_id');
            $table->timestampTz('created_at')->default($this->db->raw('CURRENT_TIMESTAMP(0)'));
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('friend_name');
            $table->string('friend_email');

            $table->index('job_id');

            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('job_refers');
    }
}
