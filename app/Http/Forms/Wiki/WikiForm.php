<?php

namespace Coyote\Http\Forms\Wiki;

use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class WikiForm extends Form implements ValidatesWhenSubmitted
{
    protected $theme = self::THEME_INLINE;

    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * @param WikiRepository $wiki
     */
    public function __construct(WikiRepository $wiki)
    {
        $this->wiki = $wiki;
    }

    public function buildForm()
    {
        $this
            ->add('title', 'text', [
                'rules' => 'required|string|min:1|max:200',
                'label' => 'Tytuł'
            ])
            ->add('parent_id', 'select', [
                'label' => 'Strona macierzysta',
                'choices' => $this->getTreeList(),
                'empty_value' => '--'
            ])
            ->add('long_title', 'text', [
                'rules' => 'string|max:200',
                'label' => 'Rozszerzony tytuł',
                'help' => 'Rozszerzony tytuł będzie widoczny na pasku tytułu w przeglądarce.'
            ])
            ->add('excerpt', 'textarea', [
                'rules' => 'string|max:255',
                'label' => 'Zajawka',
                'help' => 'Skrócony opis tekstu, wstęp.',
                'attr' => [
                    'rows' => 3
                ]
            ])
            ->add('text', 'markdown', [
                'rules' => 'string',
                'attr' => [
                    'data-paste-url' => ''
                ]
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Zapisywanie...'
                ]
            ]);
    }

    /**
     * @return array
     */
    protected function getTreeList()
    {
        return $this->wiki->treeList();
    }
}
