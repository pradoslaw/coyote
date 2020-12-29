<?php

namespace Coyote\Http\Resources\Api;

class ForumResource extends \Coyote\Http\Resources\ForumResource
{
    public function toArray($request)
    {
        $parent = parent::toArray($request);

        // backward compatibility ... :(
        if (isset($parent[0]->data['topic'])) {
            $topic = &$parent[0]->data['topic'];

            $topic['subject'] = $topic['title'];
            unset($topic['title']);
        }

        return $parent;
    }
}
