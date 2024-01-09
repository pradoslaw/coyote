<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBeforeFeatureInsertTrigger extends Migration
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
CREATE OR REPLACE FUNCTION before_feature_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	NEW."order" := (SELECT COALESCE(MAX("order"), 0) FROM features) + 1;

	RETURN NEW;
END;$$;

CREATE TRIGGER before_feature_insert BEFORE INSERT ON features FOR EACH ROW EXECUTE PROCEDURE "before_feature_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "before_feature_insert" ON features;');
        $this->db->unprepared('DROP FUNCTION before_feature_insert();');
    }
}
