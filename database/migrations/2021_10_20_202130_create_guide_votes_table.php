<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuideVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guide_votes', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumInteger('guide_id');
            $table->mediumInteger('user_id');
            $table->timestampTz('created_at')->useCurrent();
            $table->string('ip', 45);

            $table->index(['guide_id', 'user_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('guide_id')->references('id')->on('guides')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guide_votes');
    }
}
