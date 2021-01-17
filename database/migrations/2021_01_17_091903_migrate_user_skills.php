<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateUserSkills extends Migration
{
    use SchemaBuilder;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $skills = \Coyote\User\Skill::all();

        foreach ($skills as $skill) {
            $tag = \Coyote\Tag::firstOrCreate(['name' => $skill->name]);

            $skill->tag_id = $tag->id;
            $skill->rate = ceil($skill->rate / 2);
            $skill->save();
        }

        Schema::table('user_skills', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->db->statement('UPDATE user_skills SET tag_id = null, rate = FLOOR(rate)');
    }
}
