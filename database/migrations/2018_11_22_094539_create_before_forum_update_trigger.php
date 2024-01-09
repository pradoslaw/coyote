<?php

use Illuminate\Database\Migrations\Migration;

class CreateBeforeForumUpdateTrigger extends Migration
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
CREATE OR REPLACE FUNCTION before_forum_update() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
    IF COALESCE(NEW.parent_id, 0) != COALESCE(OLD.parent_id, 0) THEN
	    NEW."order" := (SELECT COALESCE(MAX("order"), 0) FROM forums WHERE COALESCE(parent_id, 0) = COALESCE(NEW.parent_id, 0)) + 1;
	    
	    UPDATE forums SET "order" = "order" - 1 WHERE COALESCE(parent_id, 0) = COALESCE(OLD.parent_id, 0) AND "order" > OLD."order";
	END IF;

	RETURN NEW;
END;$$;

CREATE TRIGGER before_forum_update BEFORE UPDATE ON forums FOR EACH ROW EXECUTE PROCEDURE "before_forum_update"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "before_forum_update" ON forums;');
        $this->db->unprepared('DROP FUNCTION before_forum_update();');
    }
}
