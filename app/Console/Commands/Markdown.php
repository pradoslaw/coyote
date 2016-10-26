<?php

namespace Coyote\Console\Commands;

use Coyote\Services\Markdown\Transformer;
use Illuminate\Console\Command;
use DB;

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
}
