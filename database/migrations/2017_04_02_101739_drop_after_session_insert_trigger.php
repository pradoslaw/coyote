<?php

use Illuminate\Database\Migrations\Migration;

class DropAfterSessionInsertTrigger extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS  after_session_insert ON sessions');
        $this->db->unprepared('DROP FUNCTION IF EXISTS after_session_insert()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
