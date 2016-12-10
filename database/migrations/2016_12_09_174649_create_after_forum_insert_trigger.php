<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAfterForumInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE FUNCTION after_forum_insert() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
    INSERT INTO forum_orders (forum_id, user_id, "order")
    SELECT NEW.id, user_id, NEW."order"
    FROM forum_orders
    GROUP BY user_id;    

	RETURN NEW;
END;$$;

CREATE TRIGGER after_forum_insert AFTER INSERT ON forums FOR EACH ROW EXECUTE PROCEDURE "after_forum_insert"();
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS "after_forum_insert" ON forums;');
        DB::unprepared('DROP FUNCTION after_forum_insert();');
    }
}
