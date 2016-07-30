<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wiki_subscribers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wiki_id');
            $table->mediumInteger('user_id');
            // moze byc NULL (kompatybilnosc wsteczna)
            $table->timestampTz('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP(0)'));

            $table->unique(['wiki_id', 'user_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('wiki_id')->references('id')->on('wiki_pages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wiki_subscribers');
    }
}
