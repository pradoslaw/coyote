<?php

namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\Form;
use Coyote\Tag\Category;
use Illuminate\Validation\Rule;

class TagForm extends Form
{
    /**
     * It's public so we can use use attr from twig
     *
     * @var array
     */
    public $attr = [
        'method' => self::POST,
        'enctype' => 'multipart/form-data'
    ];


    public function buildForm()
    {
        $categoriesList = Category::pluck('name', 'id')->toArray();

        $this
            ->add('name', 'text', [
                'rules' => [
                    'required',
                    'string',
                    'max:40',
                    'tag',
                    Rule::unique('tags')->ignore($this->data->id)
                ],
                'label' => 'Nazwa (skrócona)',
                'help' => 'Tylko podstawowe znaki, małe litery i cyfry.'

            ])
            ->add('real_name', 'text', [
                'rules' => 'string|max:100',
                'label' => 'Nazwa (pełna)',

            ])
            ->add('category_id', 'select', [
                'label' => 'Kategoria',
                'choices' => $categoriesList,
                'empty_value' => '--',
                'rules' => [
                    'nullable',
                    'int'
                ]

            ])
            ->add('logo', 'file', [
                'label' => 'Logo',
                'rules' => 'mimes:jpeg,jpg,png,gif'
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Zapisywanie...'
                ]
            ]);
    }

    public function messages()
    {
        return ['name.unique' => 'Tag o tej nazwie już istnieje.'];
    }
}
