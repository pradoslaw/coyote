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
            $table->string('name');
            $table->timestampsTz();
            $table->softDeletes();
            $table->text('description');
            $table->text('requirements');
            $table->text('recruitment');
            $table->tinyInteger('is_remote');
            $table->smallInteger('country_id')->nullable();
            $table->smallInteger('salary_from')->nullable();
            $table->smallInteger('salary_to')->nullable();
            $table->smallInteger('currency_id')->nullable();
            $table->smallInteger('rate_id')->nullable();
            $table->smallInteger('employment_id')->nullable();
            $table->timestampTz('deadline_at');
            $table->string('email')->nullable();
            $table->tinyInteger('incognito')->default(0);
            $table->tinyInteger('apply_enable')->default(1);
            $table->float('score')->default(0);
            $table->mediumInteger('order')->default(0);

            $table->foreign('firm_id')->references('id')->on('firms')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->foreign('employment_id')->references('id')->on('job_employment');
            $table->foreign('rate_id')->references('id')->on('job_rates');
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
