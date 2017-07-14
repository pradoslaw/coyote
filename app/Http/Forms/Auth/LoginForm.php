<?php

namespace Coyote\Http\Forms\Auth;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class LoginForm extends Form implements ValidatesWhenSubmitted
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
     * @var \Coyote\User
     */
    protected $user;

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
                'label' => 'Logowanie',
                'attr' => [
                    'data-submit-state' => 'Logowanie...'
                ]
            ]);
    }

    /**
     * @return \Coyote\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param ValidationFactory $factory
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(ValidationFactory $factory)
    {
        $validator = $this->makeValidatorInstance($factory);

        $validator->after(function ($validator) {
            $this->user = $this->userRepository->findByName(mb_strtolower($this->request->get('name')));

            if (!$this->user) {
                $validator->errors()->add('name', trans('validation.user_exist'));
            } else {
                if (!$this->user->is_active || $this->user->is_blocked) {
                    $validator->errors()->add('name', trans('validation.user_active'));
                }

                if (!$this->user->hasAccessByIp($this->request->ip())) {
                    $validator->errors()->add('name', trans('validation.user_access'));
                }
            }
        });

        return $validator;
    }
}
