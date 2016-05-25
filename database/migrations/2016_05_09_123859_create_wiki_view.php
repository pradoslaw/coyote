<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWikiView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE VIEW "wiki_view" AS 
 SELECT wiki_paths.parent_id,
    wiki_paths.path,
    wiki_paths.id AS path_id,
    wiki.id,
    wiki.title,
    wiki.long_title,
    wiki.slug,
    wiki.created_at,
    wiki.updated_at,
    wiki.deleted_at,
    wiki.excerpt,
    wiki.text,
    wiki.is_locked,
    wiki.template
   FROM (wiki_paths
     JOIN wiki ON ((wiki.id = wiki_paths.wiki_id)));');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP VIEW wiki_view');
    }
}
