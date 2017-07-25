<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobIndustriesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('job_industries', function (Blueprint $table) {
            $table->smallInteger('industry_id');
            $table->integer('job_id');

            $table->index('job_id');

            $table->foreign('industry_id')->references('id')->on('industries')->onDelete('cascade');
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
        $this->schema->drop('job_industries');
    }
}
