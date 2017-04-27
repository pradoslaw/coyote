<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGuestIdToUsersTable extends Migration
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
            $table->uuid('guest_id')->nullable();
        });

        $this->db->table('users')->orderBy('id')->chunk(10000, function ($users) {
            foreach ($users as $user) {
                $this->db->table('users')->where('id', $user->id)->update(['guest_id' => (string)Ramsey\Uuid\Uuid::uuid4()]);
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
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn(['guest_id']);
        });
    }
}
