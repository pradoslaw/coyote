<?php

use Illuminate\Database\Migrations\Migration;

class CreateAfterFeatureDeleteTrigger extends Migration
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
CREATE OR REPLACE FUNCTION after_features_delete() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	UPDATE features SET "order" = "order" - 1 WHERE "order" > OLD."order";

	RETURN NEW;
END;$$;

CREATE TRIGGER after_features_delete AFTER DELETE ON features FOR EACH ROW EXECUTE PROCEDURE "after_features_delete"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_features_delete" ON features;');
        DB::unprepared('DROP FUNCTION after_features_delete();');
    }
}
