<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->mediumInteger('user_id');
            $table->timestampTz('created_at')->useCurrent();
            $table->morphs('resource');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $this->db->table('topic_subscribers')->orderBy('id')->chunk(10000, function ($result) {
            $data = [];

            foreach ($result as $row) {
                $data[] = ['user_id' => $row->user_id, 'created_at' => $row->created_at, 'resource_id' => $row->topic_id, 'resource_type' => 'Coyote\Topic'];
            }

            $this->db->table('subscriptions')->insert($data);
        });

        $this->db->table('post_subscribers')->orderBy('id')->chunk(10000, function ($result) {
            $data = [];

            foreach ($result as $row) {
                $data[] = ['user_id' => $row->user_id, 'created_at' => $row->created_at, 'resource_id' => $row->post_id, 'resource_type' => 'Coyote\Post'];
            }

            $this->db->table('subscriptions')->insert($data);
        });

        $this->db->table('microblog_subscribers')->orderBy('id')->chunk(10000, function ($result) {
            $data = [];

            foreach ($result as $row) {
                $data[] = ['user_id' => $row->user_id, 'created_at' => $row->created_at, 'resource_id' => $row->microblog_id, 'resource_type' => 'Coyote\Microblog'];
            }

            $this->db->table('subscriptions')->insert($data);
        });

        $this->db->table('job_subscribers')->orderBy('id')->chunk(10000, function ($result) {
            $data = [];

            foreach ($result as $row) {
                $data[] = ['user_id' => $row->user_id, 'created_at' => $row->created_at, 'resource_id' => $row->job_id, 'resource_type' => 'Coyote\Job'];
            }

            $this->db->table('subscriptions')->insert($data);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
