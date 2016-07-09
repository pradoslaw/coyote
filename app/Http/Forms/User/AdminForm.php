<?php

namespace Coyote\Http\Forms\User;

use Coyote\Repositories\Contracts\GroupRepositoryInterface as GroupRepository;
use Illuminate\Contracts\Auth\Access\Gate;

class AdminForm extends SettingsForm
{
    /**
     * @var GroupRepository
     */
    protected $group;

    /**
     * @var Gate
     */
    protected $gate;

    /**
     * @param GroupRepository $group
     * @param Gate $gate
     */
    public function __construct(GroupRepository $group, Gate $gate)
    {
        parent::__construct();

        $this->group = $group;
        $this->gate = $gate;
    }

    public function buildForm()
    {
        parent::buildForm();

        $this->add('skills', 'collection', [
            'label' => 'Umiejętności',
            'child_attr' => [
                'type' => 'child_form',
                'class' => SkillsForm::class,
                'value' => $this->data
            ]
        ]);

        $this->addAfter('group_id', 'is_confirm', 'checkbox', [
            'label' => 'Potwierdzony adres e-mail',
            'rules' => 'bool'
        ]);

        $this->addAfter('group_id', 'is_active', 'checkbox', [
            'label' => 'Konto aktywne',
            'rules' => 'bool'
        ]);

        $groups = $this->group->pluck('name', 'id')->toArray();

        $this->add('groups', 'choice', [
            'label' => 'Grupy użytkownika',
            'choices' => $groups,
            'property' => 'id'
        ]);
    }
}
