<?php

use Illuminate\Database\Migrations\Migration;

class ChangeWikiChildrenFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION wiki_children(_parent_id INTEGER DEFAULT NULL) RETURNS TABLE (depth INTEGER, node INTEGER[], root_id INTEGER, id INTEGER, parent_id INTEGER, wiki_id INTEGER, path TEXT, title VARCHAR(255), long_title VARCHAR(255), slug VARCHAR(255), created_at TIMESTAMPTZ, updated_at TIMESTAMPTZ, deleted_at TIMESTAMP, excerpt TEXT, is_locked SMALLINT, template VARCHAR(255), views INTEGER, children BIGINT) AS $$
    WITH RECURSIVE nodes AS (
        (
            SELECT 1 AS depth, ARRAY[path_id] AS node, path_id AS root_id, *
            FROM   wiki_paths
            WHERE  (CASE WHEN _parent_id IS NULL THEN parent_id IS NULL ELSE parent_id = _parent_id END)
        )

        UNION ALL

        SELECT r.depth + 1, r.node || n.path_id, r.root_id, n.*
        FROM   nodes r
        JOIN   wiki_paths n ON n.parent_id = r.path_id
    )
    SELECT
        depth,
        node,
        root_id,
        nodes.path_id AS id,
        nodes.parent_id,
        wiki_pages.id AS wiki_id,
        nodes.path,
        wiki_pages.title,
        wiki_pages.long_title,
        wiki_pages.slug,
        wiki_pages.created_at,
        wiki_pages.updated_at,
        nodes.deleted_at,
        wiki_pages.excerpt,
        wiki_pages.is_locked,
        wiki_pages.template,
        wiki_pages.views,
        COUNT (*) OVER (PARTITION BY nodes.root_id) - COUNT (*) OVER (PARTITION BY nodes.root_id ORDER BY depth) AS children
    FROM  nodes
        JOIN wiki_pages ON ((wiki_pages.id = nodes.wiki_id)) AND wiki_pages.deleted_at IS NULL
    WHERE  nodes.deleted_at IS NULL
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
        DB::unprepared('
CREATE OR REPLACE FUNCTION wiki_children(_parent_id INTEGER DEFAULT NULL) RETURNS TABLE (depth INTEGER, node INTEGER[], root_id INTEGER, id INTEGER, parent_id INTEGER, wiki_id INTEGER, path TEXT, title VARCHAR(255), long_title VARCHAR(255), slug VARCHAR(255), created_at TIMESTAMPTZ, updated_at TIMESTAMPTZ, deleted_at TIMESTAMP, excerpt TEXT, is_locked SMALLINT, template VARCHAR(255), views INTEGER, children BIGINT) AS $$
    WITH RECURSIVE nodes AS (
        (
            SELECT 1 AS depth, ARRAY[path_id] AS node, path_id AS root_id, *
            FROM   wiki_paths
            WHERE  (CASE WHEN _parent_id IS NULL THEN parent_id IS NULL ELSE parent_id = _parent_id END)
        )

        UNION ALL

        SELECT r.depth + 1, r.node || n.path_id, r.root_id, n.*
        FROM   nodes r
        JOIN   wiki_paths n ON n.parent_id = r.path_id
    )
    SELECT
        depth,
        node,
        root_id,
        nodes.path_id AS id,
        nodes.parent_id,
        wiki_pages.id AS wiki_id,
        nodes.path,
        wiki_pages.title,
        wiki_pages.long_title,
        wiki_pages.slug,
        wiki_pages.created_at,
        wiki_pages.updated_at,
        nodes.deleted_at,
        wiki_pages.excerpt,
        wiki_pages.is_locked,
        wiki_pages.template,
        wiki_pages.views,
        COUNT (*) OVER (PARTITION BY nodes.root_id) - COUNT (*) OVER (PARTITION BY nodes.root_id ORDER BY depth) AS children
    FROM  nodes
        JOIN wiki_pages ON ((wiki_pages.id = nodes.wiki_id))
    WHERE  nodes.deleted_at IS NULL
    ORDER  BY node;

$$ LANGUAGE sql;
        ');
    }
}
