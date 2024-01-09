<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfterMicroblogTagsDelete extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_microblog_tags_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET microblogs = microblogs - 1 WHERE id = OLD.tag_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_microblog_tags_delete AFTER DELETE ON microblog_tags FOR EACH ROW EXECUTE PROCEDURE "after_microblog_tags_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_microblog_tags_delete" ON microblog_tags;');
        $this->db->unprepared('DROP FUNCTION after_microblog_tags_delete();');
    }
}
