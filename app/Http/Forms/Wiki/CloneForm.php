<?php

namespace Coyote\Http\Forms\Wiki;

use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class CloneForm extends Form implements ValidatesWhenSubmitted
{
    use TreeListTrait;

    protected $theme = self::THEME_INLINE;
    protected $wiki;

    public function __construct(WikiRepository $wiki)
    {
        parent::__construct();

        $this->wiki = $wiki;
    }

    public function buildForm()
    {
        $this
            ->add('title', 'text', [
                'rules' => 'required|wiki_route|wiki_unique:0',
                'label' => 'Tytuł',
                'attr' => [
                    'readonly' => 'readonly'
                ]
            ])
            ->add('parent_id', 'select', [
                'label' => 'Nowa strona macierzysta',
                'rules' => 'sometimes|int|exists:wiki_paths,path_id',
                'choices' => $this->getTreeList(),
                'empty_value' => '--',
                'value' => ''
            ])
            ->add('submit', 'submit', [
                'label' => 'Kopiuj',
                'attr' => [
                    'data-submit-state' => 'Kopiowanie...'
                ]
            ]);
    }
}
