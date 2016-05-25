<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiParentsFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION wiki_parents(_path_id INTEGER) RETURNS SETOF wiki_view AS $$
    WITH RECURSIVE node_rec AS (
        (
            SELECT *
            FROM   wiki_view
            WHERE  path_id = _path_id
        )
        
        UNION ALL
        
        SELECT n.*
        FROM   node_rec r 
        JOIN   wiki_view    n ON n.path_id = r.parent_id
    )
    SELECT *
    FROM   node_rec
$$ LANGUAGE sql;        
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION wiki_parents(INTEGER)');
    }
}
