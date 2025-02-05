<?php
namespace Coyote\Feature\DraftPost;

use Illuminate\Database\Connection;

readonly class DraftPostService
{
    public function __construct(private Connection $connection) {}

    public function insert(string $text, int $topicId, string $guestId): void
    {
        $this->connection->table('post_drafts')->insert([
            'text'     => $text,
            'topic_id' => $topicId,
            'guest_id' => $guestId,
        ]);
    }

    public function fetchDrafts(string $guestId): array
    {
        $drafts = $this->connection->table('post_drafts')
            ->where('guest_id', '=', $guestId)
            ->get()
            ->all();
        $result = [];
        foreach ($drafts as $draft) {
            $result[] = [$draft->topic_id, $draft->text];
        }
        return $result;
    }

    public function fetchDraft(string $guestId): ?array
    {
        $first = $this->connection->table('post_drafts')->where('guest_id', $guestId)->first();
        if ($first) {
            return [$first->topic_id, $first->text];
        }
        return null;
    }

    public function removeDrafts(string $guestId): void
    {
        $this->connection->table('post_drafts')
            ->where('guest_id', '=', $guestId)
            ->delete();
    }
}
