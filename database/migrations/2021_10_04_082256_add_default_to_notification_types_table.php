<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultToNotificationTypesTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_types', function (Blueprint $table) {
            $table->json('default')->nullable();
            $table->dropColumn('type');
        });

        $types = \Coyote\Notification\Type::all();

        foreach ($types as $type) {
            $payload = ['push'];

            if ($type->profile) {
                $payload[] = 'db';
            }

            if ($type->email) {
                $payload[] = 'mail';
            }

            $type->default = json_encode($payload);
            $type->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_types', function (Blueprint $table) {
            $table->dropColumn('default');
            $table->string('type')->nullable();
        });
    }
}
