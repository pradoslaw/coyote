<?php
namespace Coyote\Console\Commands;

use Coyote\Post;
use Coyote\Repositories\Eloquent\PostRepository;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\User;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class UserTargetCommand extends Command
{
    protected $signature = 'coyote:target {username}';
    protected $description = 'Gather statistics about user';

    public function __construct(
        private PostRepository $posts,
        private UserRepository $users)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        return $this->handleUser($this->user($this->argument('username')));
    }

    public function handleUser(User $user): int
    {
        $this->display($this->joined(
            $this->acceptedPosts($user),
            $this->postsAndVotes($user),
            $this->votesGiven($user),
            $this->comments($user)
        ));
        return 0;
    }

    private function display(array $value): void
    {
        echo \json_encode($value, \JSON_PRETTY_PRINT) . PHP_EOL;
    }

    private function user(string $username): User
    {
        $user = $this->users->findByName($username);
        if ($user === null) {
            throw new \Exception("Failed to find user by name: $username");
        }
        return $user;
    }

    private function joined(array ...$values): array
    {
        return $this->groupBy(\array_merge(...$values), 'forum');
    }

    private function groupBy(array $array, string $key): array
    {
        $result = [];
        foreach ($array as $element) {
            $result[$element[$key]][] = $element;
        }
        foreach ($result as &$value) {
            $value = \array_merge(...$value) + [
                    'posts_accepted' => 0,
                    'posts'          => 0,
                    'votes_received' => 0,
                    'votes_given'    => 0,
                    'comments'       => 0,
                ];
            \kSort($value);
            unset($value['forum']);
        }
        return $result;
    }

    private function acceptedPosts(User $user): array
    {
        return Post::select([
            'forums.slug AS forum',
            new Expression('COUNT(posts.id) AS posts_accepted')
        ])
            ->join('post_accepts', 'post_accepts.post_id', '=', 'posts.id')
            ->join('topics', 'topics.id', '=', 'posts.topic_id')
            ->join('forums', 'forums.id', '=', 'posts.forum_id')
            ->join('users', 'users.id', '=', 'post_accepts.user_id')
            ->where('posts.user_id', $user->id)
            ->groupBy('forums.slug')
            ->get()
            ->toArray();
    }

    private function postsAndVotes(User $user): array
    {
        return Post::select([
            'forums.slug as forum',
            new Expression('COUNT(posts.id) AS posts'),
            new Expression('SUM(posts.score) AS votes_received')
        ])
            ->join('forums', 'forums.id', '=', 'posts.forum_id')
            ->where('posts.user_id', $user->id)
            ->groupBy('posts.forum_id')
            ->groupBy('forums.slug')
            ->groupBy('forums.name')
            ->get()
            ->toArray();
    }

    private function votesGiven(User $user): array
    {
        return Post\Vote::select([
            'forums.slug as forum',
            new Expression('COUNT(post_votes.id) AS votes_given')
        ])
            ->where('user_id', $user->id)
            ->join('forums', 'forums.id', '=', 'post_votes.forum_id')
            ->groupBy('forums.slug')
            ->get()
            ->toArray();
    }

    private function comments(User $user): array
    {
        $toArray = Db::table('post_comments')
            ->select([
                'forums.slug as forum',
                new Expression('COUNT(post_comments.id) AS comments')
            ])
            ->where('post_comments.user_id', $user->id)
            ->join('posts', 'posts.id', '=', 'post_comments.post_id')
            ->join('forums', 'forums.id', '=', 'posts.forum_id')
            ->groupBy('forums.slug')
            ->get()
            ->toArray();
        return \array_map([$this, 'cast'], $toArray);
    }

    private function cast(\stdClass $object): array
    {
        return (array)$object;
    }
}
