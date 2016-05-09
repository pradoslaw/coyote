<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiChildrenFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION wiki_children(_parent_id INTEGER DEFAULT NULL, _depth INTEGER DEFAULT 10) RETURNS TABLE (depth INTEGER, node INTEGER[], id INTEGER, parent_id INTEGER, title VARCHAR(255), long_title VARCHAR(255), slug VARCHAR(255), path TEXT, created_at TIMESTAMPTZ, updated_at TIMESTAMPTZ, deleted_at TIMESTAMP, excerpt TEXT, "text" TEXT, is_locked SMALLINT, template VARCHAR(255)) AS $$
    WITH RECURSIVE node_rec AS (
        (
            SELECT 1 AS depth, ARRAY[id] AS node, *
            FROM   wiki
            WHERE  (CASE WHEN _parent_id IS NULL THEN parent_id IS NULL ELSE parent_id = _parent_id END)
        )
            
        UNION ALL
        SELECT r.depth + 1, r.node || n.id, n.*
        FROM   node_rec r 
        JOIN   wiki    n ON n.parent_id = r.id
        WHERE  r.depth < _depth
    )
    SELECT *
    FROM   node_rec
    ORDER  BY node;

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
        DB::unprepared('DROP FUNCTION wiki_children(INTEGER, INTEGER)');
    }
}
