<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameIsMarkedInNotificationsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('notifications', function (Blueprint $table) {
            $table->renameColumn('is_marked', 'is_clicked');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('notifications', function (Blueprint $table) {
            $table->renameColumn('is_clicked', 'is_marked');
        });
    }
}
