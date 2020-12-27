<?php

use Coyote\Notification;
use Coyote\Notification\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecordToNotificationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Type::unguard();
        Type::updateOrCreate(['id' => Notification::MICROBLOG_DELETE], [
            'name' => '...usunięcie Twojego wpisu',
            'headline' => 'Wpis został usunięty przez {sender}',
            'profile' => true,
            'email' => true,
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
        Type::where('id', Notification::MICROBLOG_DELETE)->delete();
    }
}
