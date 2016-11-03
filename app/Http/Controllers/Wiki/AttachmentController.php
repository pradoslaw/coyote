<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Factories\MediaFactory;
use Coyote\Wiki\Attachment;
use Coyote\Http\Controllers\AttachmentController as BaseController;

class AttachmentController extends BaseController
{
    use MediaFactory;

    /**
     * @param array $attributes
     * @return Attachment
     */
    protected function create(array $attributes)
    {
        return Attachment::create($attributes);
    }
}
