<?php

namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;
use Coyote\Tag\Category;

class TagForm extends Form implements ValidatesWhenSubmitted
{
    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'rules' => 'required|string|max:40|tag',
                'label' => 'Nazwa (skrócona)',
            ])
            ->add('real_name', 'text', [
                'rules' => 'string|max:100',
                'label' => 'Nazwa (pełna)',
            ])
            ->add('category_id', 'select', [
                'label' => 'Kategoria',
                'choices' => Category::pluck('name', 'id')->toArray(),
                'empty_value' => '--'
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Zapisywanie...'
                ]
            ]);
    }
}
