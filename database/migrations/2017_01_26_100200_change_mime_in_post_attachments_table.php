<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeMimeInPostAttachmentsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('post_attachments', function (Blueprint $table) {
            $table->string('mime', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('post_attachments', function (Blueprint $table) {
            $table->string('mime', 50)->change();
        });
    }
}
