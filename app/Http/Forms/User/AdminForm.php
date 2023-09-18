<?php

namespace Coyote\Http\Forms\User;

use Coyote\Repositories\Contracts\GroupRepositoryInterface;
use Coyote\Services\Geocoder\GeocoderInterface;

class AdminForm extends SettingsForm
{
    public function __construct(GeocoderInterface $geocoder, private GroupRepositoryInterface $group)
    {
        parent::__construct($geocoder);
    }

    public function buildForm():void
    {
        $this->add('name', 'text', [
            'label' => 'Nazwa użytkownika',
            'rules' => 'required|min:2|max:28|username|user_unique:' . $this->getData()->id,
        ]);

        parent::buildForm();

        $this
            ->addAfter('group_id', 'is_confirm', 'checkbox', [
                'label' => 'Potwierdzony adres e-mail',
                'rules' => 'bool'
            ])
            ->addAfter('group_id', 'is_sponsor', 'checkbox', [
                'label' => 'Sponsor',
                'rules' => 'bool'
            ])
            ->addAfter('group_id', 'marketing_agreement', 'checkbox', [
                'label' => 'Zgoda marketingowa',
                'rules' => 'bool'
            ])
            ->addAfter('group_id', 'is_active', 'checkbox', [
                'label' => 'Konto aktywne',
                'rules' => 'bool'
            ])
            ->addAfter('allow_sticky_header', 'delete_photo', 'checkbox', [
                'label' => 'Usuń zdjęcie'
            ])
            ->add('groups', 'choice', [
                'label'    => 'Grupy użytkownika',
                'choices'  => $this->group->pluck('name', 'id'),
                'property' => 'id'
            ]);
    }
}
