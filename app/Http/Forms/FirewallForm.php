<?php
namespace Coyote\Http\Forms;

use Carbon\Carbon;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\FormEvents;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;
use Coyote\User;
use Illuminate\Http\Request;

class FirewallForm extends Form implements ValidatesWhenSubmitted
{
    public function __construct(Request $request, private UserRepository $repository)
    {
        parent::__construct();
        $this->addEventListener(FormEvents::PRE_RENDER, function (Form $form) use ($request) {
            $userId = $this->data->user_id ?? $request->input('user');
            if (!empty($userId)) {
                $user = $this->repository->find($userId, ['name']);
                if (!empty($user)) {
                    $form->get('name')->setValue($user->name);
                }
            }
            if (empty($this->data->id)) {
                $this->get('expire_at')->setValue(Carbon::now()->addDay(1));
                $this->get('ip')->setValue($request->input('ip'));
            } else {
                $form->get('expire_at')->setValue(Carbon::parse($form->get('expire_at')->getValue())->format('Y-m-d'));
            }
        });
        $this->addEventListener(FormEvents::PRE_SUBMIT, fn(Form $form) => $form->remove('created_at'));
        $this->addEventListener(FormEvents::POST_SUBMIT, function (Form $form) {
            $username = $form->get('name')->getValue();
            $form->add('user_id', 'hidden', ['template' => 'hidden']);
            if ($username) {
                /** @var User $user */
                $user = $this->repository->findByName($username);
                if ($user) {
                    $form->get('user_id')->setValue($user->id);
                }
            }
        });
    }

    public function buildForm(): void
    {
        $this
            ->add('name', 'text', [
                'rules' => 'nullable|string|user_exist',
                'label' => 'Nazwa użytkownika',
                'attr'  => [
                    'id'           => 'username',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('ip', 'text', [
                'label' => 'IP',
                'rules' => 'nullable|string|min:2|max:100',
                'help'  => 'IP może zawierać znak *',
            ])
            ->add('fingerprint', 'text', [
                'label' => 'Fingerprint',
                'rules' => 'nullable|string|max:255',
            ])
            ->add('reason', 'textarea', [
                'label' => 'Powód',
                'rules' => 'max:1000',
            ])
            ->add('created_at', 'text', [
                'label' => 'Data utworzenia',
                'attr'  => ['disabled' => 'disabled'],
            ])
            ->add('expire_at', 'date', [
                'label' => 'Data wygaśnięcia',
                'rules' => 'required_if:lifetime,0|date_format:Y-m-d',
                'attr'  => [
                    'id' => 'expire-at',
                ],
            ])
            ->add('lifetime', 'checkbox', [
                'label'   => 'Bezterminowo',
                'checked' => empty($this->data->expire_at),
            ])
            ->add('submit', 'submit_with_delete', [
                'label'             => 'Zapisz',
                'attr'              => [
                    'data-submit-state' => 'Zapisywanie...',
                ],
                'delete_url'        => empty($this->data->id) ? '' : route('adm.firewall.delete', [$this->data->id]),
                'delete_visibility' => !empty($this->data->id),
            ]);
    }

    public function messages(): array
    {
        return ['expire_at.required_if' => 'To pole jest wymagane.'];
    }
}
