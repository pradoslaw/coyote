<?php

use Illuminate\Database\Migrations\Migration;

class SetUpdatedAtToTriggers extends Migration
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
 	UPDATE tags SET microblogs = microblogs + 1, last_used_at = now(), updated_at = now() WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;
        ');

        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_microblog_tags_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET microblogs = microblogs - 1, updated_at = now() WHERE id = OLD.tag_id;

	RETURN NEW;
END;$$;
        ');

        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_job_tags_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET jobs = jobs + 1, last_used_at = now(), updated_at = now() WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;
        ');

        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_job_tags_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET jobs = jobs - 1, updated_at = now() WHERE id = OLD.tag_id;

	RETURN NEW;
END;$$;
        ');

        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_topic_tags_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET topics = topics + 1, updated_at = now() WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;
        ');

        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_topic_tags_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET topics = topics - 1, updated_at = now() WHERE id = OLD.tag_id;

	RETURN NEW;
END;$$;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_microblog_tags_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET microblogs = microblogs + 1, last_used_at = now() WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;
        ');

        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_microblog_tags_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET microblogs = microblogs - 1 WHERE id = OLD.tag_id;

	RETURN NEW;
END;$$;
        ');

        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_job_tags_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET jobs = jobs + 1, last_used_at = now() WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;
        ');

        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_job_tags_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET jobs = jobs - 1 WHERE id = OLD.tag_id;

	RETURN NEW;
END;$$;
        ');

        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_topic_tags_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET topics = topics + 1 WHERE id = NEW.tag_id;

	RETURN NEW;
END;$$;
        ');

        $this->db->unprepared('
CREATE OR REPLACE FUNCTION after_topic_tags_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE tags SET topics = topics - 1 WHERE id = OLD.tag_id;

	RETURN NEW;
END;$$;
        ');
    }
}
