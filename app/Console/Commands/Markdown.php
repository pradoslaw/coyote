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
        $result = DB::connection('mysql')
            ->table('microblog')
            ->when($id, function ($builder) use ($id) {
                return $builder->where('microblog_id', $id);
            });

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

        $this->transformer->quote = $this->getQuotes();

        DB::connection('mysql')
            ->table('post')
            ->select(['post.post_id', 'text_content AS post_content'])
            ->join('post_text', 'text_id', '=', 'post_text')
            ->orderBy('post_id', 'DESC')
            ->when($id, function ($builder) use ($id) {
                return $builder->where('post_id', $id);
            })
            ->chunk(
                100000,
                function ($sql) use ($bar) {
                    foreach ($sql as $row) {
                        DB::table('posts')
                            ->where('id', $row->post_id)
                            ->update(['text' => $this->transformer->transform($row->post_content)]);

                        $bar->advance();
                    }
                }
            );

        $bar->finish();
    }

    private function getQuotes()
    {
        return DB::connection('mysql')
            ->table('post')
            ->select(DB::raw('post_id, IF(user_id > 1, user_name, post_username) AS name'))
            ->leftJoin('user', 'user_id', '=', 'post_user')
            ->lists('name', 'post_id');
    }

    private function postLog($id = null)
    {
        $count = DB::connection('mysql')->table('post_text')->count();
        $bar = $this->output->createProgressBar($count);

        $this->transformer->quote = $this->getQuotes();

        DB::connection('mysql')
            ->table('post_text')
            ->select(['text_id', 'text_content AS post_content'])
            ->orderBy('text_id', 'DESC')
            ->when($id, function ($builder) use ($id) {
                return $builder->where('text_id', $id);
            })
            ->chunk(
                100000,
                function ($sql) use ($bar) {
                    foreach ($sql as $row) {
                        DB::table('post_log')
                            ->where('id', $row->text_id)
                            ->update(['text' => $this->transformer->transform($row->post_content)]);

                        $bar->advance();
                    }
                }
            );

        $bar->finish();
    }

    private function postComment($id)
    {
        $count = DB::connection('mysql')->table('post_comment')->count();
        $bar = $this->output->createProgressBar($count);

        DB::connection('mysql')
            ->table('post_comment')
            ->select(['comment_id', 'comment_text'])
            ->when($id, function ($builder) use ($id) {
                return $builder->where('comment_id', $id);
            })
            ->orderBy('comment_id', 'DESC')
            ->chunk(
                100000,
                function ($sql) use ($bar) {
                    foreach ($sql as $row) {
                        DB::table('post_comments')
                            ->where('id', $row->comment_id)
                            ->update(['text' => $this->transformer->transform($row->comment_text)]);

                        $bar->advance();
                    }
                }
            );

        $bar->finish();
    }

    private function wikiComment($id)
    {
        $this->transformer = new Transformer();

        $sql = DB::connection('mysql')->table('comment')
            ->select(['comment_id', 'comment_content'])
            ->where('comment_module', 3)
            ->where('comment_user', '>', 0)
            ->when($id, function ($builder) use ($id) {
                return $builder->where('comment_id', $id);
            })
            ->get();

        $bar = $this->output->createProgressBar(count($sql));

        foreach ($sql as $row) {
            DB::table('wiki_comments')
                ->where('id', $row->comment_id)
                ->update(['text' => $this->transformer->transform($row->comment_content)]);

            $bar->advance();
        }

        $bar->finish();
    }

    public function job($id)
    {
        $jobs = DB::table('jobs')
            ->select(['id', 'description', 'recruitment'])
            ->when($id, function ($builder) use ($id) {
                return $builder->where('id', $id);
            })
            ->get();

        $bar = $this->output->createProgressBar(count($jobs));

        foreach ($jobs as $job) {
            $data = ['description' => $this->transformer->transform($job->description)];

            if (!empty($job->recruitment)) {
                $data['recruitment'] = $this->transformer->transform($job->recruitment);
            }

            DB::table('jobs')
                ->where('id', $job->id)
                ->update($data);

            $bar->advance();
        }

        $bar->finish();
    }

    public function user($id)
    {
        $users = DB::table('users')
            ->select(['id', 'sig'])
            ->whereNotNull('sig')
            ->when($id, function ($builder) use ($id) {
                return $builder->where('id', $id);
            })
            ->get();

        $bar = $this->output->createProgressBar(count($users));

        foreach ($users as $user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['sig' => $this->transformer->transform($user->sig)]);

            $bar->advance();
        }

        $bar->finish();
    }

    public function wiki($id)
    {
        $wiki = DB::connection('mysql')
            ->table('page')
            ->select(['page.page_id', 'page_text.text_content AS page_content'])
            ->join('page_text', 'text_id', '=', 'page_text')
            ->where('page_module', '=', 3)
            ->when($id, function ($builder) use ($id) {
                return $builder->where('page_id', $id);
            })
            ->get();

        $bar = $this->output->createProgressBar(count($wiki));

        $result = DB::table('wiki_attachments')->get();
        $attachments = [];

        foreach ($result as $row) {
            if (in_array(pathinfo($row->file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'gif', 'png'])) {
                $attachments[$row->name] = $row->file;
            } else {
                $attachments[$row->name] = $row->wiki_id . '/' . $row->id;
            }
        }

        $this->transformer->mapping = $attachments;

        foreach ($wiki as $row) {
            $wikiId = DB::table('wiki_paths')->where('path_id', $row->page_id)->value('wiki_id');
            DB::table('wiki_pages')->where('id', $wikiId)->update(['text' => $this->transformer->transform($row->page_content)]);

            $texts = DB::connection('mysql')
                ->table('page_version')
                ->select(['page_text.*'])
                ->join('page_text', 'page_text.text_id', '=', 'page_version.text_id')
                ->where('page_version.page_id', $wikiId)
                ->orderBy('text_id')
                ->get();

            foreach ($texts as $text) {
                $time = date('Y-m-d H:i:s', $text->text_time);

                DB::table('wiki_log')->where('wiki_id', $wikiId)->where('created_at', $time)->update(['text' => $this->transformer->transform($text->text_content)]);
            }

            $bar->advance();
        }

        $bar->finish();
    }

    public function pm($id)
    {
        $sql = DB::table('pm_text')
            ->select(['id', 'text'])
            ->get();

        foreach ($sql as $row) {
            DB::table('pm_text')->where('id', $row->id)->update(['text' => $this->transformer->transform($row->text)]);
        }
    }
}
