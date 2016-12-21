<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllowStickyHeaderToUsersTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->addColumn('tinyInteger', 'allow_sticky_header')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('allow_sticky_header');
        });
    }
}
