<?php

namespace Coyote\Repositories\Eloquent\Post;

use Coyote\Repositories\Contracts\Post\AttachmentRepositoryInterface;
use Coyote\Repositories\Eloquent\Repository;

class AttachmentRepository extends Repository implements AttachmentRepositoryInterface
{
    /**
     * @return \Coyote\Post\Attachment
     */
    public function model()
    {
        return 'Coyote\Post\Attachment';
    }

    /**
     * Find attachments by file name
     *
     * @param $file
     * @return mixed
     */
    public function findByFile($file)
    {
        if (!is_array($file)) {
            $file = [$file];
        }

        return $this->model->whereIn('file', $file)->get();
    }
}
