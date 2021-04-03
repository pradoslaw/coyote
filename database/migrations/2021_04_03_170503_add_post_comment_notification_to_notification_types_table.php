<?php

use Coyote\Notification;
use Coyote\Notification\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPostCommentNotificationToNotificationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Type::unguard();
        Type::updateOrCreate(['id' => Notification::POST_COMMENT_MIGRATED], [
            'name' => '...zamianie komentarza na post',
            'headline' => 'Komentarz został zamieniony na post przez {sender}',
            'profile' => true,
            'email' => true,
            'category' => 'Forum'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Type::where('id', \Coyote\Notification::POST_COMMENT_MIGRATED)->delete();
    }
}
