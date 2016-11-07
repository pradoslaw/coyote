<?php

namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\Form;

class AttachmentForm extends Form
{
    /**
     * @var string
     */
    protected $theme = self::THEME_INLINE;

    /**
     * @var string
     */
    protected $template = 'attachment';

    /**
     * @var bool
     */
    protected $enableValidation = false;

    /**
     * @var string
     */
    protected $downloadRoute = 'wiki.download';

    /**
     * @var array
     *
     * @todo ustawiamy formularz jako HTTP PUT przez co nie bedzie walidowany podczas dodawania zalacznikow.
     * to jest bug i nie powinno byc to konieczne. do poprawy!
     */
    public $attr = ['method' => 'PUT'];

    /**
     * @return string
     */
    public function getDownloadRoute(): string
    {
        return $this->downloadRoute;
    }

    /**
     * @param string $downloadRoute
     * @return $this
     */
    public function setDownloadRoute(string $downloadRoute)
    {dd($downloadRoute);
        $this->downloadRoute = $downloadRoute;

        return $this;
    }

    public function buildForm()
    {
        $this
            ->add('id', 'hidden')
            ->add('file', 'hidden')
            ->add('name', 'control')
            ->add('mime', 'control')
            ->add('created_at', 'control')
            ->add('size', 'control');
//            ->add('url', 'hidden', [
//                'value' => route($this->downloadRoute, [$this->data->id])
//            ]);
    }
}
