<?php

use Illuminate\Database\Migrations\Migration;

class AddRecordToNotificationTypesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Coyote\Notification\Type::forceCreate(['id' => \Coyote\Notification::JOB_CREATE,
            'name' => '...o dodanej ofercie pracy',
            'headline' => 'Dodano nową ofertę pracy',
            'category' => 'Praca',
            'profile' => true,
            'email' => true,
            'is_public' => false
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Coyote\Notification\Type::destroy(\Coyote\Notification::JOB_CREATE);
    }
}
