<?php

namespace Coyote\Http\Forms\User;

use Coyote\Repositories\Contracts\GroupRepositoryInterface as GroupRepository;
use Coyote\Services\Geocoder\GeocoderInterface;
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
     * @param GeocoderInterface $geocoder
     * @param GroupRepository $group
     * @param Gate $gate
     */
    public function __construct(GeocoderInterface $geocoder, GroupRepository $group, Gate $gate)
    {
        parent::__construct($geocoder);

        $this->group = $group;
        $this->gate = $gate;
    }

    public function buildForm()
    {
        $this->add('name', 'text', [
            'label' => 'Nazwa użytkownika',
            'rules' => 'required|min:2|max:28|username|user_unique:' . $this->getData()->id,
        ]);

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
