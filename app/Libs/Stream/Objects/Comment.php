<?php

namespace Coyote\Stream\Objects;

use Coyote\Microblog as Model;

class Comment extends Object
{
    public $objectType = 'comment';

    public function map($object)
    {
        $class = class_basename($object);
        if (!method_exists($this, $class)) {
            throw new \Exception("There is not method called $class");
        }

        $this->$class($object);

        return $this;
    }

    private function microblog($microblog)
    {
        $this->id = $microblog->id;
        $this->url = route('microblog.view', [$microblog->parent_id], false) . '#comment-' . $microblog->id;
        $this->displayName = excerpt($microblog->text);
    }
}
