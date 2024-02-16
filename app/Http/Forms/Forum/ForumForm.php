<?php
namespace Coyote\Http\Forms\Forum;

use Coyote\Repositories\Eloquent\ForumRepository;
use Coyote\Repositories\Eloquent\GroupRepository;
use Coyote\Services\FormBuilder\Form;

class ForumForm extends Form
{
    public function __construct(private readonly ForumRepository $forum, private readonly GroupRepository $group)
    {
        parent::__construct();
    }

    public function buildForm(): void
    {
        $this
            ->add('name', 'text', [
                'label' => 'Nazwa',
                'rules' => 'required|string|max:50',
            ])
            ->add('title', 'text', [
                'label' => 'Rozszerzony tytuł',
                'rules' => 'nullable|string|max:200',
            ])
            ->add('slug', 'text', [
                'label' => 'Ścieżka',
                'rules' => 'required|string|max:50',
            ])
            ->add('parent_id', 'select', [
                'label'       => 'Kategoria macierzysta',
                'choices'     => $this->getParentList(),
                'empty_value' => '--',
            ])
            ->add('description', 'textarea', [
                'label' => 'Opis',
                'rules' => 'required|string|max:255',
            ])
            ->add('url', 'text', [
                'label' => 'Przekierowanie do strony WWW',
                'rules' => 'nullable|url',
                'help'  => 'Jeżeli ustawisz to pole, po wejściu na forum, użytkownik będzie przekierowywane pod ten URL.',
            ])
            ->add('section', 'text', [
                'label' => 'Nazwa sekcji',
                'rules' => 'nullable|string|max:50',
                'help'  => 'Jeżeli zostawisz to pole puste, sekcja nie ukaże się w menu nawigacyjnym.',
            ])
            ->add('is_locked', 'checkbox', [
                'label' => 'Forum zablokowane',
            ])
            ->add('require_tag', 'checkbox', [
                'label' => 'Wymagaj podania co najmniej 1 tagu',
            ])
            ->add('enable_reputation', 'checkbox', [
                'label' => 'Zliczaj reputację w tej kategorii',
            ])
            ->add('enable_anonymous', 'checkbox', [
                'label' => 'Zezwalaj na tworzenie postów bez logowania',
            ])
            ->add('enable_tags', 'checkbox', [
                'label' => 'Zezwalaj na dodawanie tagów',
            ])
            ->add('enable_homepage', 'checkbox', [
                'label' => 'Zezwalaj na wyświetlanie na stronie głównej',
            ])
            ->add('access', 'choice', [
                'label'    => 'Dostęp dla grup',
                'choices'  => $this->getGroupsList(),
                'property' => 'group_id',
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr'  => ['data-submit-state' => 'Wysyłanie...'],
            ]);
    }

    private function getParentList(): array
    {
        return $this
            ->forum
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get()
            ->filter(function ($item) {
                return $item->id !== $this->data->id;
            })
            ->pluck('name', 'id')
            ->toArray();
    }

    private function getGroupsList(): array
    {
        return $this->group->pluck('name', 'id');
    }
}
