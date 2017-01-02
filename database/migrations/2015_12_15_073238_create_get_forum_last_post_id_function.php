<?php

use Illuminate\Database\Migrations\Migration;

class CreateGetForumLastPostIdFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION "public"."get_forum_last_post_id"(_forum_id INTEGER)
RETURNS INTEGER AS $BODY$
BEGIN
	RETURN (SELECT MAX("id") FROM posts WHERE forum_id = _forum_id AND deleted_at IS NULL);
END
$BODY$
  LANGUAGE \'plpgsql\' VOLATILE COST 100
;";
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION get_forum_last_post_id(INTEGER);');
    }
}
