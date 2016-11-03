<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Wiki\Attachment;
use Coyote\Http\Controllers\AttachmentController as BaseAttachmentController;

class AttachmentController extends BaseAttachmentController
{
    /**
     * @param array $attributes
     * @return Attachment
     */
    protected function create(array $attributes)
    {
        return Attachment::create($attributes);
    }
}
