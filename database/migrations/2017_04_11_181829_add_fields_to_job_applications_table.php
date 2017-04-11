<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToJobApplicationsTable extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('job_applications', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('github')->nullable();
            $table->text('text')->nullable();
            $table->string('salary')->nullable();
            $table->string('dismissal_period')->nullable();
            // max filename length is 255. extra space for uniq slug
            $table->string('cv', 300)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'phone', 'github', 'text', 'salary', 'dismissal_period', 'cv']);
        });
    }
}
