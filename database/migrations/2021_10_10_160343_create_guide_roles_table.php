<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuideRolesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guide_roles', function (Blueprint $table) {
            $table->id();
            $table->integer('guide_id')->index();
            $table->integer('user_id');
            $table->timestampTz('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
        });

        $sql = "ALTER TABLE guide_roles ADD COLUMN seniority seniority DEFAULT NULL";
        $this->db->unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guide_roles');
    }
}
