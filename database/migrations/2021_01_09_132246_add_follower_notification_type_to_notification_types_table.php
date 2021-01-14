<?php

use Coyote\Notification;
use Illuminate\Database\Migrations\Migration;
use Coyote\Notification\Type;

class AddFollowerNotificationTypeToNotificationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Type::unguard();
        Type::updateOrCreate(['id' => Notification::FOLLOWER,
            'name' => '...aktywności obserwowanego użytkownika',
            'headline' => 'Nowy {type} od: {sender}',
            'profile' => true,
            'email' => false,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Type::where('id', \Coyote\Notification::FOLLOWER)->delete();
    }
}
