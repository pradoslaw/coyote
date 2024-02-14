<?php
namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class PastebinForm extends Form implements ValidatesWhenSubmitted
{
    public function buildForm(): void
    {
        $this
            ->add('text', 'textarea', [
                'rules'      => 'required|string',
                'theme'      => self::THEME_INLINE,
                'attr'       => ['id' => 'code',],
                'label_attr' => ['style' => 'display: none',],
            ]);

        if (!empty($this->request->user())) {
            if (!empty($this->getData()->id) && $this->request->user()->can('pastebin-delete')) {
                $this->add('del', 'button', [
                    'label' => 'Usuń',
                    'attr'  => [
                        'id'          => 'btn-delete',
                        'class'       => 'btn btn-danger',
                        'data-toggle' => "modal",
                        'data-target' => "#confirm",
                    ],
                ]);
            }
        }
    }
}
