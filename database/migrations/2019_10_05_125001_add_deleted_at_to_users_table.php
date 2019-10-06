<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtToUsersTable extends Migration
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
            $table->softDeletesTz();
        });

        $this->db->statement('UPDATE users SET deleted_at = updated_at WHERE is_active = 0');

        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
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
            $table->smallInteger('is_active')->default(0);
        });

        $this->db->statement('UPDATE users SET is_active = 0 WHERE deleted_at IS NOT NULL');

        $this->schema->table('users', function (Blueprint $table) {
            $table->dropSoftDeletesTz();
        });
    }
}
