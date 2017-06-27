<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlanIdToJobsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('jobs', function (Blueprint $table) {
            $table->smallInteger('plan_id')->nullable();

            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('jobs', function (Blueprint $table) {
            $table->dropColumn(['plan_id']);
        });
    }
}
