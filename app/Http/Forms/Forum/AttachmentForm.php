<?php

namespace Coyote\Http\Forms\Forum;

use Coyote\User;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Services\FormBuilder\Form;
use Coyote\Topic;

class AttachmentForm extends Form
{
    /**
     * @var string
     */
    protected $theme = 'forum.forms';

    /**
     * @var string
     */
    protected $template = 'attachments';

    /**
     * @var \Coyote\Post\Attachment[]
     */
    public $attachments;

    /**
     * @param array|mixed $data
     * @param bool $rebuildForm
     * @return $this
     */
    public function setData($data, $rebuildForm = true)
    {
        $this->attachments = $data;
        return parent::setData($data, $rebuildForm);
    }

    public function buildForm()
    {
        $this->add('attachments', 'hidden', [
            'template' => 'attachment'
        ]);
    }
}
