<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlansColumnsToJobsTable extends Migration
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
            $table->addColumn('integer', 'plan_id')->nullable();
            $table->addColumn('smallInteger', 'boost')->default(0);

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
            $table->dropForeign('jobs_plan_id_foreign');

            $table->dropColumn(['plan_id', 'boost']);
        });
    }
}
