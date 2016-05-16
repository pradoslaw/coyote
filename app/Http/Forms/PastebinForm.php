<?php

namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class PastebinForm extends Form implements ValidatesWhenSubmitted
{
    public function buildForm()
    {
        $this
            ->add('text', 'textarea', [
                'rules' => 'required|string',
                'theme' => self::THEME_INLINE,
                'attr' => [
                    'id' => 'code'
                ],
                'label_attr' => [
                    'style' => 'display: none'
                ]
            ])
            ->add('title', 'text', [
                'label' => 'Nazwa',
                'rules' => 'required|string|min:2|max:100',
                'help' => 'Nazwa, tytuł wpisu. Może to być po prostu Twój nick.'
            ])
            ->add('mode', 'select', [
                'choices' => $this->getModeList(),
                'label' => 'Kolorowanie składni',
                'empty_value' => '--',
                'rules' => 'sometimes|in:' . implode(',', array_keys($this->getModeList()))
            ])
            ->add('expires', 'select', [
                'choices' => $this->getExpiresList(),
                'label' => 'Wygaśnie',
                'empty_value' => 'Nigdy',
                'rules' => 'sometimes|in:' . implode(',', array_keys($this->getExpiresList())),
                'value' => 72,
                'help' => 'Po upływie tego czasu, ten wpis zostanie automatycznie usunięty.'
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Zapisywanie...'
                ]
            ])
            ->add('human_email', 'text', [
                'rules' => 'max:0', // anti spam bot detection
                'row_attr' => [
                    'style' => 'display: none'
                ]
            ]);

        if (!empty($this->request->user())) {
            $this->get('title')->setValue($this->request->user()->name);
        }
    }

    /**
     * @return array
     */
    private function getModeList()
    {
        return [
            'c_cpp' => 'C++',
            'csharp' => 'C#',
            'css' => 'CSS',
            'pascal' => 'Delphi',
            'diff' => 'Diff',
            'java' => 'Java',
            'jsx' => 'JavaFX',
            'javascript' => 'JavaScript',
            'perl' => 'Perl',
            'powershell' => 'PowerShell',
            'php' => 'PHP',
            'python' => 'Python',
            'ruby' => 'Ruby',
            'scala' => 'Scala',
            'sql' => 'SQL',
            'xml' => 'XML'
        ];
    }

    /**
     * @return array
     */
    private function getExpiresList()
    {
        return [72 => '72 godz.', 48 => '48 godz.', 24 => '24 godz.', 1 => '1 godz.'];
    }
}
