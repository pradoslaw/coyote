<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeJobsTable extends Migration
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
            $table->renameColumn('boost', 'is_boost');
            $table->smallInteger('is_publish')->default(0);
            $table->smallInteger('is_ads')->default(0);
            $table->smallInteger('is_highlight')->default(0);
            $table->smallInteger('is_on_top')->default(0);

            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('no action');
        });

        $this->db->statement('UPDATE jobs SET is_ads = 1, is_highlight = 1, is_on_top = 1 WHERE is_boost = 1');
        $this->db->statement('UPDATE jobs SET is_publish = 1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('jobs', function (Blueprint $table) {
            $table->dropColumn(['plan_id', 'is_publish', 'is_ads', 'is_highlight', 'is_on_top']);
            $table->renameColumn('is_boost', 'boost');
        });
    }
}
