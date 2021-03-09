<?php

namespace Coyote\Http\Forms\User;

use Coyote\Services\FormBuilder\Form;

class SkillsForm extends Form
{
    const RATE_LABELS = ['Słabo', 'Podstawy', 'Przeciętnie', 'Dobrze', 'Bardzo dobrze', 'Ekspert'];

    public function buildForm()
    {
        $this->setAttr(['id' => 'rate-form']);

        $this
            ->add('name', 'text', [
                'label' => 'Nazwa',
                'rules' => ['required', 'string', 'max:100'],
                'attr' => [
                    'placeholder' => 'Np. java, c#'
                ]
            ])
            ->add('priority', 'text', [
                'label' => 'Ocena',
                'rules' => 'required|integer|min:1|max:3'
            ])
            ->add('order', 'hidden');
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'name.required'     => 'Proszę wpisać nazwę umiejętności',
            'name.unique'       => 'Taka umiejętność znajduje się już na Twojej liście.',
            'rate.min'          => 'Nie wprowadziłeś oceny swojej umiejętności.'
        ];
    }
}
