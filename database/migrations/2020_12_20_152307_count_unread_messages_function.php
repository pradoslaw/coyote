<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CountUnreadMessagesFunction extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->db->unprepared('
CREATE OR REPLACE FUNCTION count_unread_messages(_user_id INTEGER DEFAULT NULL) RETURNS INTEGER AS $$
BEGIN
    RETURN (
        SELECT coalesce(count(distinct (author_id)), 0)
        FROM pm
        WHERE pm.user_id = _user_id AND pm.folder = 1 AND read_at IS NULL
    );
END
$$ LANGUAGE plpgsql;
        ');

        $this->db->unprepared('UPDATE users SET pm_unread = count_unread_messages(id) WHERE pm > 0');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->unprepared('DROP FUNCTION count_unread_messages');
        $this->db->unprepared('UPDATE users SET pm_unread = (SELECT COUNT(*) FROM pm WHERE pm.user_id = users.id AND pm.folder = 1 AND read_at IS NULL) WHERE pm > 0');
    }
}
