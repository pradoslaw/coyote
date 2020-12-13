<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->unique();
            $table->string('email');
            $table->string('salt', 34)->nullable();
            $table->string('password', 64)->nullable();
            $table->string('provider', 50)->nullable();
            $table->string('provider_id', 64)->nullable();
            $table->rememberToken();
            $table->timestampsTz();
            $table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('is_confirm')->default(0);
            $table->tinyInteger('is_blocked')->default(0);
            $table->mediumInteger('group_id')->nullable();
            $table->string('date_format', 32)->default('%Y-%m-%d %H:%M');
            $table->string('timezone')->default('Europe/Warsaw');
            $table->timestampTz('visited_at')->nullable();
            $table->mediumInteger('visits')->default(0);
            $table->string('ip', 45)->nullable();
            $table->string('browser', 1000)->nullable();
            $table->string('access_ip')->nullable();
            $table->tinyInteger('alert_login')->default(0);
            $table->tinyInteger('alert_failure')->default(1);
            $table->mediumInteger('reputation')->default(0)->index();
            $table->string('photo', 20)->nullable();
            $table->mediumInteger('alerts')->default(0);
            $table->mediumInteger('pm')->default(0);
            $table->mediumInteger('alerts_unread')->default(0);
            $table->mediumInteger('pm_unread')->default(0);
            $table->mediumInteger('posts')->default(0);
            $table->string('sig', 500)->nullable();
            $table->string('bio', 500)->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->mediumInteger('birthyear')->nullable();
            $table->string('firm', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->tinyInteger('allow_count')->default(1);
            $table->tinyInteger('allow_smilies')->default(0);
            $table->tinyInteger('allow_sig')->default(1);
            $table->tinyInteger('allow_subscribe')->default(1);

            $table->unique(['provider', 'provider_id']);
        });

        DB::unprepared('CREATE INDEX "users_name_index" ON "users" USING btree (LOWER(name))');
        DB::unprepared('CREATE INDEX "users_email_index" ON "users" USING btree (LOWER(email))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
