<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobFeaturesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('job_features', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_id');
            $table->smallInteger('feature_id');
            $table->string('name')->nullable();
            $table->tinyInteger('is_checked')->default(0);

            $table->index('job_id');

            $table->foreign('feature_id')->references('id')->on('features')->onDelete('cascade');
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
        $this->schema->drop('job_features');
    }
}
