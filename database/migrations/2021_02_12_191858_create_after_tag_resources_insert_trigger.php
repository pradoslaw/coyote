<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfterTagResourcesInsertTrigger extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->db->unprepared("
CREATE OR REPLACE FUNCTION after_tag_resources_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
    EXECUTE format('UPDATE tags
    SET resources = jsonb_set(resources, ''{%s}'', (COALESCE(resources->>''%s'', ''0'')::int + 1)::text::jsonb), last_used_at = now()
    WHERE id = %s', REPLACE(NEW.resource_type, '\', '\\\'), NEW.resource_type, NEW.tag_id);

	RETURN NEW;
END;$$;

CREATE TRIGGER after_tag_resources_insert AFTER INSERT ON tag_resources FOR EACH ROW EXECUTE PROCEDURE after_tag_resources_insert();
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_tag_resources_insert" ON tag_resources;');
        $this->db->unprepared('DROP FUNCTION after_tag_resources_insert();');
    }
}
