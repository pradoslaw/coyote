<?php

namespace Coyote\Services\FormBuilder\Fields;

class SubmitWithDelete extends Field
{
    /**
     * @var string
     */
    protected $template = 'submit_with_delete';

    /**
     * @var string
     */
    protected $deleteLabel = 'Usuń';

    /**
     * @var array
     */
    protected $deleteAttr = ['data-bs-target' => '#modal-delete', 'data-bs-toggle' => 'modal'];

    /**
     * @var bool
     */
    protected $deleteVisibility = true;

    /**
     * @var string
     */
    protected $deleteUrl;

    /**
     * @return string
     */
    public function getDeleteLabel()
    {
        return $this->deleteLabel;
    }

    /**
     * @param string $deleteLabel
     */
    public function setDeleteLabel($deleteLabel)
    {
        $this->deleteLabel = $deleteLabel;
    }

    /**
     * @return boolean
     */
    public function getDeleteVisibility()
    {
        return $this->deleteVisibility;
    }

    /**
     * @param boolean $deleteVisibility
     */
    public function setDeleteVisibility($deleteVisibility)
    {
        $this->deleteVisibility = $deleteVisibility;
    }

    /**
     * @return array
     */
    public function getDeleteAttr()
    {
        return $this->deleteAttr;
    }

    /**
     * @param array $deleteAttr
     */
    public function setDeleteAttr($deleteAttr)
    {
        $this->deleteAttr = $deleteAttr;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->deleteUrl;
    }

    /**
     * @param string $deleteUrl
     */
    public function setDeleteUrl($deleteUrl)
    {
        $this->deleteUrl = $deleteUrl;
    }
}
