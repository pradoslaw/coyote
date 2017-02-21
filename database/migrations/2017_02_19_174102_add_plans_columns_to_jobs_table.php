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
            $table->addColumn('smallInteger', 'enable_plan')->default(0);
            $table->addColumn('integer', 'plan_id')->nullable();
            $table->addColumn('timestamptz', 'plan_starts_at')->nullable();
            $table->addColumn('timestamptz', 'plan_ends_at')->nullable();

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

            $table->dropColumn(['is_premium', 'plan_id', 'plan_starts_at', 'plan_ends_at']);
        });
    }
}
