<?php

namespace Coyote\Repositories\Eloquent\Post;

use Coyote\Repositories\Contracts\Post\AttachmentRepositoryInterface;
use Coyote\Repositories\Eloquent\Repository;

class AttachmentRepository extends Repository implements AttachmentRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Post\Attachment';
    }
}
