<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscribersToTopicsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('topics', function (Blueprint $table) {
            $table->smallInteger('subscribers')->default(0);
        });

        $result = $this->db->select('SELECT COUNT(*) AS count, topic_id FROM topic_subscribers GROUP BY topic_id');

        foreach ($result as $row) {
            $this->db->update('UPDATE topics SET subscribers = ? WHERE id = ?', [$row->count, $row->topic_id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('topics', function (Blueprint $table) {
            $table->dropColumn('subscribers');
        });
    }
}
