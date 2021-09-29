<?php

namespace Coyote\Http\Forms\Wiki;

use Coyote\Http\Forms\AttachmentForm;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Services\FormBuilder\Form;
use Illuminate\Contracts\Auth\Access\Gate;

class SubmitForm extends Form
{
    use TreeListTrait;

    const RULE_PARENT_ID = 'nullable|int|exists:wiki_paths,path_id';

    /**
     * @var string
     */
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
     * It's public so we can use use attr from twig
     *
     * @var array
     */
    public $attr = [
        'method' => self::POST,
        'id' => 'submit-form'
    ];

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
        $parentId = $this->request->input('parent_id');

        $this
            ->add('title', 'text', [
                'rules' => sprintf(
                    'required|string|min:1|max:200|wiki_route:%d|wiki_unique:' . ($this->data->id ?? null) . ',%d',
                    $parentId,
                    $parentId
                ),
                'label' => 'Tytuł'
            ])
            ->add('long_title', 'text', [
                'rules' => 'nullable|string|max:200',
                'label' => 'Rozszerzony tytuł',
                'help' => 'Rozszerzony tytuł będzie widoczny na pasku tytułu w przeglądarce.'
            ])
            ->add('excerpt', 'textarea', [
                'rules' => 'nullable|string|max:500',
                'label' => 'Skrócony opis artykułu',
                'help' => 'Maksymalnie 500 znaków.',
                'attr' => [
                    'rows' => 3
                ]
            ])
            ->add('text', 'textarea', [
                'rules' => 'string',
                'template' => 'textarea',
                'attr' => [
                    'data-paste-url' => '',
                    'class' => 'form-control mono'
                ],
                'row_attr' => [
                    'role' => 'tabpanel',
                    'class' => 'tab-pane active',
                    'id' => 'body'
                ]
            ])
            ->add('comment', 'text', [
                'rules' => 'nullable|string|max:255',
                'label' => 'Opis zmian',
                'help' => 'Maksymalnie 255 znaków.',
            ])
            ->add('attachments', 'collection', [
                'template' => 'attachments',
                'child_attr' => [
                    'type' => 'child_form',
                    'class' => AttachmentForm::class
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
                'rules' => 'in:' . implode(',', array_keys($this->getTemplateList())),
                'help' => 'Ten widok Twig zostanie użyty do wyświetlenia tej strony.'
            ]);
        }

        if (empty($this->getData()->id)) {
            $this->addAfter('title', 'parent_id', 'select', [
                'label' => 'Strona macierzysta',
                'rules' => self::RULE_PARENT_ID,
                'choices' => $this->getTreeList(),
                'empty_value' => '--'
            ]);

            $this->get('title')->setRules($this->get('title')->getRules() . '|reputation:1');
        } else {
            $this->add('parent_id', 'hidden', [
                'rules' => self::RULE_PARENT_ID
            ]);
        }
    }

    /**
     * @return array
     */
    public function messages()
    {
        return ['title.reputation' => 'Aby dodać nową stronę, musisz posiadać minimum 1 pkt reputacji.'];
    }

    /**
     * @return mixed
     */
    protected function getTemplateList()
    {
        $templates = ['show', 'category', 'blog.home', 'help.home', 'help.show'];
        return array_combine($templates, $templates);
    }
}
