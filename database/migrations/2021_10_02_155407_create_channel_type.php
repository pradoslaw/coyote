<?php

use Illuminate\Database\Migrations\Migration;

class CreateChannelType extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->db->unprepared('DROP TYPE IF EXISTS "channel"');
        $this->db->unprepared('CREATE TYPE "channel" AS ENUM (\'db\', \'mail\', \'push\');');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TYPE IF EXISTS "channel"');
    }
}
