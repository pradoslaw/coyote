<?php

namespace Coyote\Console\Commands;

use Coyote\Post;
use Coyote\Repositories\Contracts\StreamRepositoryInterface;
use Coyote\Topic;
use Illuminate\Console\Command;

class MigrateStreamsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:streams';

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
        $this->mongoToPostgres();
        $this->migratePostReason();
        $this->migrateMovedTopic();
        $this->migrateLockedTopic();
    }

    private function mongoToPostgres()
    {
        $db = app('db');

        $bar = $this->output->createProgressBar($db->connection('mongodb')->collection('streams')->count());
        $bar->start();

        $db->connection('mongodb')->collection('streams')->orderBy('_id')->chunk(20000, function ($results) use ($db, $bar) {
            foreach ($results as $row) {
                unset($row['_id']);

                if ($row['created_at'] instanceof \MongoDB\BSON\UTCDateTime) {
                    $row['created_at'] = $row['created_at']->toDateTime();
                }

                if (isset($row['fingerprint']) && is_array($row['fingerprint'])) {
                    $row['fingerprint'] = array_first($row['fingerprint']);
                }
                $row = $this->toJson($row);

                $db->table('streams')->insert((array) $row);
                $bar->advance();
            }
        });

        $bar->finish();
    }

    private function migratePostReason()
    {
        $posts = Post::withTrashed()->whereNotNull('deleted_at')->get();
        $stream = app(StreamRepositoryInterface::class);

        foreach ($posts as $post) {
            $result = $stream->findWhere(['object->objectType' => 'post', 'object->id' => $post->id, 'verb' => 'delete']);

            if ($result) {
                $result = $result->sortByDesc('created_at')->first();

                $post->deleter_id = array_get($result, 'actor.id');
                $post->delete_reason = array_get($result, 'object.reasonName');
                $post->save();
            }
        }
    }

    private function migrateMovedTopic()
    {
        $topics = Topic::whereNotNull('prev_forum_id')->get();
        $stream = app(StreamRepositoryInterface::class);

        foreach ($topics as $topic) {
            $result = $stream->findWhere(['object->objectType' => 'topic', 'object->id' => $topic->id, 'verb' => 'move']);

            if ($result) {
                $result = $result->sortByDesc('created_at')->first();

                $topic->mover_id = array_get($result, 'actor.id');
                $topic->moved_at = array_get($result, 'created_at');
                $topic->save();
            }
        }
    }

    private function migrateLockedTopic()
    {
        $topics = Topic::where('is_locked', 1)->get();
        $stream = app(StreamRepositoryInterface::class);

        foreach ($topics as $topic) {
            $result = $stream->findWhere(['object->objectType' => 'topic', 'object->id' => $topic->id, 'verb' => 'lock']);

            if ($result) {
                $result = $result->sortByDesc('created_at')->first();

                $topic->locker_id = array_get($result, 'actor.id');
                $topic->locked_at = array_get($result, 'created_at');
                $topic->save();
            }
        }
    }

    private function toJson($data)
    {
        foreach (['actor', 'object', 'target'] as $key) {
            $data[$key] = json_encode(!empty($data[$key]) ? $data[$key] : []);
        }

        return $data;
    }
}
