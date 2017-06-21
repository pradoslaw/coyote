<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->smallInteger('firm_id')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->timestampsTz();
            $table->softDeletes();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('recruitment')->nullable();
            $table->tinyInteger('is_remote')->default(0);
            $table->smallInteger('country_id')->nullable();
            $table->integer('salary_from')->nullable();
            $table->integer('salary_to')->nullable();
            $table->smallInteger('currency_id')->nullable();
            $table->smallInteger('rate_id')->nullable();
            $table->smallInteger('employment_id')->nullable();
            $table->timestampTz('deadline_at');
            $table->string('email')->nullable();
            $table->tinyInteger('enable_apply')->default(1);
            $table->integer('views')->default(1);
            $table->float('score')->default(0);
            $table->float('rank')->default(0);

            $table->foreign('firm_id')->references('id')->on('firms')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('jobs');
    }
}
