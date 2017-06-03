<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGuidToAlertsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('alerts', function (Blueprint $table) {
            $table->string('guid', 50)->nullable();

            $table->unique('guid');
        });

        $this->db->table('alerts')->orderBy('id')->chunk(200000, function ($result) {
            foreach ($result as $row) {
                $this->db->table('alerts')->where('id', $row->id)->update(['guid' => str_random(50)]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('alerts', function (Blueprint $table) {
            $table->dropColumn('guid');
        });
    }
}
