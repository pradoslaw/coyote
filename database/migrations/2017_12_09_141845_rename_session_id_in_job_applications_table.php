<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameSessionIdInJobApplicationsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->db->statement('UPDATE job_applications SET session_id = (SELECT guest_id FROM users WHERE users.id = user_id) WHERE user_id IS NOT NULL');

        $this->schema->table('job_applications', function (Blueprint $table) {
            $table->renameColumn('session_id', 'guest_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('job_applications', function (Blueprint $table) {
            $table->renameColumn('guest_id', 'session_id');
        });
    }
}
