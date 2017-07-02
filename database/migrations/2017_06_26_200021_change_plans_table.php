<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePlansTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('plans', function (Blueprint $table) {
            $table->double('discount')->default(0)->nullable();
            $table->smallInteger('length')->default(30);
            $table->jsonb('benefits')->nullable();
            $table->smallInteger('is_default')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('plans', function (Blueprint $table) {
            $table->dropColumn(['discount', 'length', 'benefits', 'is_default']);
        });
    }
}
