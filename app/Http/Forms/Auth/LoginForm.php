<?php

namespace Coyote\Http\Forms\Auth;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\FormBuilder\Form;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class LoginForm extends Form
{
    /**
     * @var string
     */
    protected $theme = self::THEME_INLINE;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;
    }

    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'rules' => 'required',
                'label' => 'Nazwa użytkownika',
                'attr' => [
                    'autofocus' => 'autofocus'
                ]
            ])
            ->add('password', 'password', [
                'rules' => 'required',
                'label' => 'Hasło'
            ])
            ->add('submit', 'submit', [
                'label' => 'Zaloguj się',
                'attr' => [
                    'data-submit-state' => 'Logowanie...'
                ]
            ]);
    }

    /**
     * @param ValidationFactory $factory
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(ValidationFactory $factory)
    {
        $validator = $this->makeValidatorInstance($factory);

        $validator->after(function ($validator) {
            $isEmail = filter_var($this->request->get('name'), FILTER_VALIDATE_EMAIL);
            $method = $isEmail ? 'findByEmail' : 'findByName';

            /** @var \Coyote\User $result */
            $result = $this->userRepository->$method($this->request->get('name'));

            if (!$result) {
                $validator->errors()->add('name', trans('validation.user_exist'));
            } else {
                if ($result->is_blocked) {
                    $validator->errors()->add('name', trans('validation.user_active'));
                }

                if (!$result->hasAccessByIp($this->request->ip())) {
                    $validator->errors()->add('name', trans('validation.user_access'));
                }

                // case insensitive login
                if (!$isEmail && ($result->name !== $this->request->input('name'))) {
                    $this->request->merge(['name' => $result->name]);
                }
            }
        });

        return $validator;
    }
}
