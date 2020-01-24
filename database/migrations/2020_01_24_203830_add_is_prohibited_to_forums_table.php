<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsProhibitedToForumsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('forums', function (Blueprint $table) {
            $table->smallInteger('is_prohibited')->default(0);
        });

        $this->db->statement('UPDATE forums SET is_prohibited = (CASE WHEN (SELECT COUNT(*) FROM forum_access WHERE forum_id = forums.id) > 0 THEN 1 ELSE 0 END)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('forums', function (Blueprint $table) {
            $table->dropColumn('is_prohibited');
        });
    }
}
