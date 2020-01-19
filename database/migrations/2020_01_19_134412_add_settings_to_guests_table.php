<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSettingsToGuestsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('guests', function (Blueprint $table) {
            $table->jsonb('settings')->nullable();
        });

        $this->db->table('settings')->orderBy('id')->chunk(1000, function ($result) {
            foreach ($result as $row) {
                $guest = $this->db->table('guests')->where('id', $row->guest_id)->first();

                if (empty($guest)) {
                    continue;
                }

                $jsonb = $guest->settings ? json_decode($guest->settings, true) : [];

                $jsonb[$row->name] = $row->value;

                $this->db->table('guests')->where('id', $row->guest_id)->update(['settings' => json_encode($jsonb)]);
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
        $this->schema->table('guests', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
    }
}
