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
CREATE OR REPLACE FUNCTION wiki_children(_parent_id INTEGER DEFAULT NULL) RETURNS TABLE (depth INTEGER, node INTEGER[], root_id INTEGER, parent_id INTEGER, path TEXT, id INTEGER, wiki_id INTEGER, title VARCHAR(255), long_title VARCHAR(255), slug VARCHAR(255), created_at TIMESTAMPTZ, updated_at TIMESTAMPTZ, deleted_at TIMESTAMP, excerpt TEXT, "text" TEXT, is_locked SMALLINT, template VARCHAR(255), views INTEGER, children BIGINT) AS $$
    WITH RECURSIVE nodes AS (
        (
            SELECT 1 AS depth, ARRAY[id] AS node, id AS root_id, *
            FROM   wiki
            WHERE  (CASE WHEN _parent_id IS NULL THEN parent_id IS NULL ELSE parent_id = _parent_id END)
        )
            
        UNION ALL
        
        SELECT r.depth + 1, r.node || n.id, r.root_id, n.*
        FROM   nodes r 
        JOIN   wiki n ON n.parent_id = r."id"
    )
    SELECT *, COUNT (*) OVER (PARTITION BY nodes.root_id) - COUNT (*) OVER (PARTITION BY nodes.root_id ORDER BY depth) AS children
    FROM   nodes
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
        DB::unprepared('DROP FUNCTION wiki_children(INTEGER)');
    }
}
