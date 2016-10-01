<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReputationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reputations', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('type_id');
            $table->mediumInteger('user_id');
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->smallInteger('value');
            $table->string('excerpt')->nullable();
            $table->string('url')->nullable();
            $table->json('metadata')->nullable();

            $table->index('user_id');
            // indeks potrzebny po to, aby na glownej stronie wyswietlic ranking reputacji od ostatniego
            // miesiaca, tygodnia itp. wtedy uzywany jest ten indeks
            $table->index('created_at');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('reputation_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('reputations');
    }
}
