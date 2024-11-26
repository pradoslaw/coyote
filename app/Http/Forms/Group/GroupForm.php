<?php
namespace Coyote\Http\Forms\Group;

use Coyote\Permission;
use Coyote\Services\FormBuilder\Form;
use Illuminate\Support;

class GroupForm extends Form
{
    public function buildForm(): void
    {
        $this->add('name', 'text', [
            'rules' => 'required|string|max:99',
            'label' => 'Nazwa grupy',
        ]);
        $this->add('short_name', 'text', [
            'label' => 'Skrócona nazwa grupy',
            'attr'  => ['disabled' => 'disabled'],
            'value' => $this->data->shortName(),
            'help'  => 'Wyświetlana w wąskich miejscach forum, np. sekcja "Użytkownicy online".',
        ]);
        $this->add('description', 'text', [
            'label' => 'Opis',
            'rules' => 'nullable|string',
            'help'  => 'Krótki opis grupy.',
        ]);
        $this->add('partner', 'checkbox', [
            'label' => 'Partnerzy serwisu',
        ]);
        $this->add('permissions', 'choice', [
            'label'   => 'Uprawnienia grupy',
            'choices' => $this->getPermissions(),
            'value'   => $this->getEnabledPermissionIds(),
        ]);
        $users = $this->getUsers();
        $this->add('users', 'choice', [
            'label'   => 'Członkowie grupy',
            'choices' => $users,
            'value'   => array_keys($users),
        ]);
        $this->add('submit', 'submit_with_delete', [
            'label'             => 'Zapisz',
            'attr'              => ['data-submit-state' => 'Zapisywanie...'],
            'delete_url'        => empty($this->data->id)
                ? ''
                : route('adm.groups.delete', [$this->data->id]),
            'delete_visibility' => !empty($this->data->id) && !$this->data->system,
        ]);
    }

    public function messages(): array
    {
        return ['name.required' => 'To pole nie może być puste.'];
    }

    private function getPermissions(): array
    {
        return Permission::query()->orderBy('name')->pluck('name', 'permissions.id')->toArray();
    }

    private function getUsers(): array
    {
        return $this->data->users()->pluck('name', 'id')->toArray();
    }

    private function getEnabledPermissionIds(): Support\Collection
    {
        return $this->data
            ->permissions()
            ->get()
            ->reject(fn($row) => $row->pivot->value == 0)
            ->pluck('id');
    }
}
