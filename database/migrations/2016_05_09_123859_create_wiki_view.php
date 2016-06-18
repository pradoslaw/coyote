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
CREATE OR REPLACE VIEW "wiki" AS SELECT 
    wiki_paths.parent_id,
    wiki_paths.path,
    wiki_paths.path_id AS id,
    wiki_pages.id AS wiki_id,
    wiki_pages.title,
    wiki_pages.long_title,
    wiki_pages.slug,
    wiki_pages.created_at,
    wiki_pages.updated_at,
    wiki_pages.deleted_at,
    wiki_pages.excerpt,
    wiki_pages.text,
    wiki_pages.is_locked,
    wiki_pages.template,
    wiki_pages.views
   FROM wiki_paths
   JOIN wiki_pages ON (wiki_pages.id = wiki_paths.wiki_id)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP VIEW wiki');
    }
}
