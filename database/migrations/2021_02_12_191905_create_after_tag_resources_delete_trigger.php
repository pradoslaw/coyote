<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfterTagResourcesDeleteTrigger extends Migration
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
CREATE FUNCTION after_tag_resources_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	EXECUTE format('UPDATE tags
    SET resources = jsonb_set(resources, ''{%s}'', (COALESCE(resources->>''%s'', ''0'')::int - 1)::text::jsonb)
    WHERE id = %s', REPLACE(OLD.resource_type, '\', '\\\'), OLD.resource_type, OLD.tag_id);

	RETURN OLD;
END;$$;

CREATE TRIGGER after_tag_resources_delete AFTER DELETE ON tag_resources FOR EACH ROW EXECUTE PROCEDURE after_tag_resources_delete();
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_tag_resources_delete" ON tag_resources;');
        $this->db->unprepared('DROP FUNCTION after_tag_resources_delete();');
    }
}
