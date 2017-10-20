<?php

namespace Coyote\Console\Commands;

use Coyote\Guest;
use Coyote\Services\Skills\Calculator;
use Illuminate\Console\Command;

class SetupPredictionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coyote:predictions';

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
     * @return mixed
     */
    public function handle()
    {
        $db = app('db');

        $bar = $this->output->createProgressBar($db->table('page_visits')->count());

        $db->transaction(function () use ($db, $bar) {
            $db
                ->table('page_visits')
                ->join('users', 'users.id', '=', 'user_id')
                ->join('pages', 'pages.id', '=', 'page_id')
                ->select(['user_id', 'tags', 'page_visits.visits'])
                ->orderBy('page_visits.id')
                ->whereNotNull('pages.tags')
                ->offset(480000)
                ->chunk(10000, function ($result) use ($bar) {
                    foreach ($result as $row) {
                        $bar->advance();

                        if ($row->tags == '[]') {
                            continue;
                        }

                        $keys = json_decode($row->tags, true);
                        $values = [];

                        for ($i = 1; $i <= count($keys); ++$i) {
                            $values[] = $row->visits;
                        }

                        $guest = Guest::where('user_id', $row->user_id)->first();
                        if (!$guest) {
                            continue;
                        }

                        $old = (array) $guest->interests;
                        $new = ['tags' => array_combine($keys, $values)];

                        $tags = $this->merge($old, $new);
                        $calculator = new Calculator($tags);

                        $guest->interests = $calculator->toArray();

                        $guest->save();
                    }
                });
        });

        $bar->finish();
    }

    private function merge($old, $new)
    {
        if (empty($old['tags'])) {
            return $new;
        }

        foreach ($new['tags'] as $tag => $count) {
            if (!isset($old['tags'][$tag])) {
                $old['tags'][$tag] = $count;
            } else {
                $old['tags'][$tag] = $old['tags'][$tag] + $count;
            }
        }

        return $old;
    }
}
