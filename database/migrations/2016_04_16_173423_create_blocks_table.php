<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name');
            $table->string('region')->nullable();
            $table->tinyInteger('is_enabled')->default(1);
            $table->timestampTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->timestampTz('updated_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->text('content');
            $table->smallInteger('max_reputation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('blocks');
    }
}
