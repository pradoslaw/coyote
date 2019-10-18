<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRateType extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->db->unprepared('CREATE TYPE "rate" AS ENUM (\'hourly\', \'weekly\', \'monthly\', \'yearly\');');
        $this->db->unprepared('CREATE TYPE "seniority" AS ENUM (\'student\', \'junior\', \'mid\', \'senior\', \'lead\', \'manager\');');
        $this->db->unprepared('CREATE TYPE "employment" AS ENUM (\'employment\', \'mandatory\', \'contract\', \'b2b\');');

        $sql = "ALTER TABLE jobs ADD COLUMN rate rate DEFAULT 'monthly'";
        $this->db->unprepared($sql);

        $sql = "ALTER TABLE jobs ADD COLUMN seniority seniority DEFAULT NULL";
        $this->db->unprepared($sql);

        $sql = "ALTER TABLE jobs ADD COLUMN employment employment DEFAULT 'employment'";
        $this->db->unprepared($sql);

        $sql = "UPDATE
                    jobs
                SET rate       = (CASE
                                      WHEN rate_id = 1 THEN 'monthly'::rate
                                      WHEN rate_id = 2 THEN 'yearly'::rate
                                      WHEN rate_id = 3 THEN 'weekly'::rate
                                      WHEN rate_id = 4 THEN 'hourly'::rate END),
                    seniority  = (CASE
                                      WHEN seniority_id = 1 THEN 'student'::seniority
                                      WHEN seniority_id = 2 THEN 'junior'::seniority
                                      WHEN seniority_id = 3 THEN 'mid'::seniority
                                      WHEN seniority_id = 4 THEN 'senior'::seniority
                                      WHEN seniority_id = 5 THEN 'lead'::seniority
                                      WHEN seniority_id = 6 THEN 'manager'::seniority END),
                    employment = (CASE
                                      WHEN employment_id = 1 THEN 'employment'::employment
                                      WHEN employment_id = 2 THEN 'mandatory'::employment
                                      WHEN employment_id = 3 THEN 'contract'::employment
                                      WHEN employment_id = 4 THEN 'b2b'::employment END)";

        $this->db->unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('jobs', function (Blueprint $table) {
            $table->dropColumn('rate', 'seniority', 'employment');
        });

        $this->db->unprepared('DROP TYPE "rate"');
        $this->db->unprepared('DROP TYPE "seniority"');
        $this->db->unprepared('DROP TYPE "employment"');
    }
}
