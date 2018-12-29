<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStreamsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('streams', function (Blueprint $table) {
            $table->increments('id');
            $table->timestampTz('created_at')->useCurrent();
            $table->string('verb');
            $table->string('ip')->nullable();
            $table->string('browser', 1000)->nullable();
            $table->string('fingerprint')->nullable();
            $table->jsonb('actor');
            $table->jsonb('object')->nullable();
            $table->jsonb('target')->nullable();
            $table->string('login')->nullable();
        });

        $this->db->connection('mongodb')->collection('streams')->orderBy('_id')->chunk(1000, function ($results) {
            foreach ($results as $result) {
                unset($result['_id']);

                $result['created_at'] = $result['created_at']->toDateTime();
                $result = $this->toJson($result);

                $this->db->table('streams')->insert($result);
            }
        });
    }

    private function toJson($data)
    {
        foreach (['actor', 'object', 'target'] as $key) {
            if (!empty($data[$key])) {
                $data[$key] = json_encode($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('streams');
    }
}
