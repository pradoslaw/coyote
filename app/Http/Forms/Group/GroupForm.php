<?php

namespace Coyote\Http\Forms\Group;

use Coyote\Permission;
use Coyote\Services\FormBuilder\Form;

class GroupForm extends Form
{
    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'rules' => 'required|string|max:99',
                'label' => 'Nazwa grupy'
            ])
            ->add('description', 'text', [
                'label' => 'Opis',
                'rules' => 'sometimes|string',
                'help' => 'Krótki opis grupy.'
            ])
            ->add('partner', 'checkbox', [
                'label' => 'Partnerzy serwisu'
            ])
            ->add('permissions', 'choice', [
                'label' => 'Uprawnienia grupy',
                'choices' => $this->getPermissions(),
                'value' => $this->getEnabledPermissions()
            ])
            ->add('submit', 'submit_with_delete', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Zapisywanie...'
                ],
                'delete_url' => empty($this->data->id) ? '' : route('adm.groups.delete', [$this->data->id]),
                'delete_visibility' => !empty($this->data->id) && !$this->data->system
            ]);
    }

    /**
     * @return array
     */
    public function messages()
    {
        return ['name.required' => 'To pole nie może być puste.'];
    }

    /**
     * @return mixed
     */
    private function getPermissions()
    {
        return Permission::pluck('name', 'permissions.id')->toArray();
    }

    /**
     * @return mixed
     */
    private function getEnabledPermissions()
    {
        return $this->data->permissions()->get()->reject(function ($row) {
            return $row->pivot->value == 0;
        })
        ->pluck('id');
    }
}
