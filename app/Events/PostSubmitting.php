<?php

namespace Coyote\Events;

use Coyote\Http\Forms\Forum\PostForm;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class PostSubmitting
{
    use Dispatchable, SerializesModels;

    /**
     * @var PostForm
     */
    public $form;

    /**
     * @param PostForm $form
     */
    public function __construct(PostForm $form)
    {
        $this->form = $form;
    }
}
