<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameMediaInMicroblogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('microblogs', function (Blueprint $table) {
            $table->renameColumn('media', 'media_backup');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('microblogs', function (Blueprint $table) {
            $table->renameColumn('media_backup', 'media');
        });
    }
}
