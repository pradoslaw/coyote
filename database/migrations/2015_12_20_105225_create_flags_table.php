<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flags', function (Blueprint $table) {
            $table->smallInteger('id', true);
            $table->smallInteger('type_id');
            $table->mediumInteger('user_id');
            $table->string('url');
            $table->json('metadata')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->softDeletes();
            $table->text('text')->nullable();
            $table->mediumInteger('moderator_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('moderator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('flag_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('flags');
    }
}
