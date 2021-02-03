<?php

namespace Coyote\Console\Commands;

use Illuminate\Console\Command;

class FixSkillsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:skills';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $skills = \Coyote\User\Skill::whereNotNull('name')->get();

        foreach ($skills as $skill) {
            if (mb_strtolower($skill->name) !== $skill->name) {
                $name = mb_strtolower($skill->name);
                $tag = \Coyote\Tag::withTrashed()->firstOrCreate(['name' => $name]);

                $skill->tag_id = $tag->id;
                $skill->save();

                $this->info($skill->name);
            }

        }

        return 0;
    }
}
