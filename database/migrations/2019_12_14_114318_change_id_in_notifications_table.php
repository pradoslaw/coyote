<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeIdInNotificationsTable extends Migration
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
            $table->rename('notifications_backup');
        });

        $this->schema->table('notification_senders', function (Blueprint $table) {
            $table->rename('notification_senders_backup');
        });

        $this->schema->create('notifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->smallInteger('type_id');
            $table->mediumInteger('user_id');
            $table->timestampTz('created_at')->default($this->db->raw('CURRENT_TIMESTAMP(0)'));
            $table->string('subject');
            $table->string('excerpt')->nullable();
            $table->string('url')->nullable();
            $table->string('object_id');
            $table->timestampTz('read_at')->nullable();
            $table->tinyInteger('is_clicked')->default(0);
            $table->integer('content_id')->nullable();
            $table->string('content_type')->nullable();

            $table->primary('id');
            $table->index(['user_id', $this->db->raw('created_at DESC')],
                'notifications_user_id_created_at desc_index');
            $table->index('object_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('notification_types');
        });

        $this->schema->create('notification_senders', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('notification_id');
            $table->mediumInteger('user_id')->nullable();
            $table->timestampTz('created_at')->default($this->db->raw('CURRENT_TIMESTAMP(0)'));
            $table->string('name')->nullable();

            $table->index('notification_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
        });

        $this->db->statement('INSERT INTO notifications SELECT guid::uuid AS id, type_id, user_id, created_at, subject, excerpt, url, object_id, read_at, is_clicked FROM notifications_backup ORDER BY id ASC');

        $this->db->statement('INSERT INTO notification_senders (notification_id, user_id, created_at, name)
                SELECT notifications_backup.guid::uuid AS notification_id, notification_senders_backup.user_id, notification_senders_backup.created_at, name
                FROM notification_senders_backup
                 LEFT JOIN notifications_backup ON notifications_backup.id = notification_senders_backup.notification_id
                  ORDER BY notifications_backup.id ASC');

        $this->db->statement('CREATE TRIGGER after_notification_insert AFTER INSERT ON notifications FOR EACH ROW EXECUTE PROCEDURE "after_notification_insert"();');
        $this->db->statement('CREATE TRIGGER after_notification_update AFTER UPDATE ON notifications FOR EACH ROW EXECUTE PROCEDURE "after_notification_update"();');
        $this->db->statement('CREATE TRIGGER after_notification_delete AFTER DELETE ON notifications FOR EACH ROW EXECUTE PROCEDURE "after_notification_delete"();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->drop('notification_senders');
        $this->schema->drop('notifications');

        $this->schema->table('notifications_backup', function (Blueprint $table) {
            $table->rename('notifications');
        });

        $this->schema->table('notification_senders_backup', function (Blueprint $table) {
            $table->rename('notification_senders');
        });
    }
}
