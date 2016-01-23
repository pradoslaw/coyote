<?php

namespace Coyote\Repositories\Contracts\Post;

use Coyote\Repositories\Contracts\RepositoryInterface;

interface AttachmentRepositoryInterface extends RepositoryInterface
{
    /**
     * Find attachments by file name
     *
     * @param $file
     * @return mixed
     */
    public function findByFile($file);
}
