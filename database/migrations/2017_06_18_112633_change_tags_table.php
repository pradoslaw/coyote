<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTagsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('tags', function (Blueprint $table) {
            $table->string('real_name')->nullable();
            $table->smallInteger('category_id')->nullable();
            $table->string('logo', 25)->nullable();

            $table->index('category_id');
            $table->foreign('category_id')->references('id')->on('tag_categories')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('tags', function (Blueprint $table) {
            $table->dropColumn(['real_name', 'category_id', 'logo']);
        });
    }
}
