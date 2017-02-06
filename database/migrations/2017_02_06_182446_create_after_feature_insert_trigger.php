<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAfterFeatureInsertTrigger extends Migration
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
CREATE FUNCTION after_feature_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 	NEW."order" := (SELECT COALESCE(MAX("order"), 0) FROM features) + 1;

	RETURN NEW;
END;$$;

CREATE TRIGGER after_feature_insert AFTER INSERT ON features FOR EACH ROW EXECUTE PROCEDURE "after_feature_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP TRIGGER IF EXISTS "after_feature_insert" ON features;');
        $this->db->unprepared('DROP FUNCTION after_feature_insert();');
    }
}
