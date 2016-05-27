<?php

namespace Coyote\Http\Forms\Wiki;

use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;
use Illuminate\Contracts\Auth\Access\Gate;

class WikiForm extends Form implements ValidatesWhenSubmitted
{
    protected $theme = self::THEME_INLINE;

    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * @var Gate
     */
    protected $gate;

    /**
     * @param WikiRepository $wiki
     * @param Gate $gate
     */
    public function __construct(WikiRepository $wiki, Gate $gate)
    {
        parent::__construct();

        $this->wiki = $wiki;
        $this->gate = $gate;
    }

    public function buildForm()
    {
        $this
            ->add('title', 'text', [
                'rules' => 'required|string|min:1|max:200',
                'label' => 'Tytuł'
            ])
            ->add('long_title', 'text', [
                'rules' => 'string|max:200',
                'label' => 'Rozszerzony tytuł',
                'help' => 'Rozszerzony tytuł będzie widoczny na pasku tytułu w przeglądarce.'
            ])
            ->add('excerpt', 'textarea', [
                'rules' => 'string|max:500',
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

        if ($this->gate->allows('wiki-admin')) {
            $this->addAfter('excerpt', 'is_locked', 'checkbox', [
                'label' => 'Zablokuj tę stronę do dalszej edycji',
                'help' => 'Tylko osoby z odpowiednim uprawnieniem będa mogły edytować tę stronę.'
            ]);

            $this->addAfter('text', 'template', 'select', [
                'label' => 'Szablon',
                'choices' => $this->getTemplateList(),
                'help' => 'Ten widok Twig zostanie użyty do wyświetlenia tej strony.'
            ]);
        }

        if (empty($this->getData()->id)) {
            $this->addAfter('title', 'path_id', 'select', [
                'label' => 'Strona macierzysta',
                'rules' => 'sometimes|int|exists:wiki_paths,path_id',
                'choices' => $this->getTreeList(),
                'empty_value' => '--',
                'value' => $this->request->input('pathId')
            ]);
        }
    }

    /**
     * @return array
     */
    protected function getTreeList()
    {
        return $this->wiki->treeList();
    }

    /**
     * @return mixed
     */
    protected function getTemplateList()
    {
        $templates = ['show', 'category', 'blog.show', 'help.show'];
        return array_combine($templates, $templates);
    }
}
