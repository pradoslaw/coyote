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
        Type::updateOrCreate(['id' => Notification::MICROBLOG_SUBSCRIBER,
            'name' => '...wpisie obserwowanego użytkownika na mikroblogu',
            'headline' => '{sender} dodał wpis na mikroblogu',
            'profile' => true,
            'email' => false,
            'category' => 'Mikroblogi'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Type::where('id', \Coyote\Notification::MICROBLOG_SUBSCRIBER)->delete();
    }
}
