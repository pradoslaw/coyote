<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfterMicroblogTagsInsert extends Migration
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
CREATE OR REPLACE FUNCTION after_microblog_tags_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET microblogs = microblogs + 1, last_used_at = now() WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_microblog_tags_insert AFTER INSERT ON microblog_tags FOR EACH ROW EXECUTE PROCEDURE "after_microblog_tags_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_microblog_tags_insert" ON microblog_tags;');
        $this->db->unprepared('DROP FUNCTION after_microblog_tags_insert();');
    }
}
