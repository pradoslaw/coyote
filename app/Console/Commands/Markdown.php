<?php

namespace Coyote\Console\Commands;

use Coyote\Services\Markdown\Transformer;
use Illuminate\Console\Command;
use DB;

ini_set('memory_limit', '1G');

class Markdown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coyote:markdown {--model=} {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Coyote markdown converter';

    /**
     * @var Transformer
     */
    private $transformer;

    public function handle()
    {
        $this->transformer = new Transformer();

        $model = $this->option('model');
        $id = $this->option('id');

        try {
            $this->$model($id);
        } catch (\Exception $e) {
            $this->error(sprintf('[%d] [%s] %s', $e->getLine(), $e->getFile(), $e->getMessage()));
        }
    }

    private function microblog($id = null)
    {
        $result = DB::connection('mysql')->table('microblog');

        if ($id) {
            $result = $result->where('microblog_id', $id);
        }

        $result = $result->get(['microblog_id', 'microblog_text']);
        $bar = $this->output->createProgressBar(count($result));

        foreach ($result as $row) {
            DB::table('microblogs')
                ->where('id', $row->microblog_id)
                ->update(['text' => $this->transformer->transform($row->microblog_text)]);

            $bar->advance();
        }

        $bar->finish();
    }

    private function post($id = null)
    {
        $count = DB::connection('mysql')->table('post')->count();
        $bar = $this->output->createProgressBar($count);

        $sql = DB::connection('mysql')
            ->table('post')
            ->select(['post.post_id', 'text_content AS post_content'])
            ->join('post_text', 'text_id', '=', 'post_text')
            ->orderBy('post_id', 'DESC')
            ->offset(7863)
            ->when($id, function ($builder) use ($id) {
                return $builder->where('post_id', $id);
            });

        $bar->advance(7863);

        $this->transformer->quote = DB::connection('mysql')
            ->table('post')
            ->select(DB::raw('post_id, IF(user_id > 1, user_name, post_username) AS name'))
            ->leftJoin('user', 'user_id', '=', 'post_user')
            ->lists('name', 'post_id');

        $sql->chunk(50000, function ($sql) use ($bar) {
            foreach ($sql as $row) {
                try {
                    DB::table('posts')
                        ->where('id', $row->post_id)
                        ->update(['text' => $this->transformer->transform($row->post_content)]);

                    $bar->advance();
                } catch (\Exception $e) {
                    var_dump($row->post_content);
                    var_dump($row->post_id);

                    throw $e;
                }
            }
        });

        $bar->finish();
    }
}
