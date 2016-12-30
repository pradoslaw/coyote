<?php

namespace Coyote\Http\Forms\Auth;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class RegisterForm extends Form implements ValidatesWhenSubmitted
{
    /**
     * @var string
     */
    protected $theme = self::THEME_INLINE;

    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'rules' => 'required|min:2|max:28|username|user_unique',
                'label' => 'Nazwa użytkownika',
                'attr' => [
                    'autofocus' => 'autofocus'
                ]
            ])
            ->add('password', 'password', [
                'rules' => 'required|confirmed|min:3',
                'label' => 'Hasło'
            ])
            ->add('password_confirmation', 'password', [
                'label' => 'Hasło (powtórnie)'
            ])
            ->add('email', 'text', [
                'rules' => 'required|email|max:255|email_unique',
                'label' => 'E-mail',
                'help' => 'Nie wysyłamy reklam. Twój e-mail nie zostanie nikomu udostępniony.'
            ])
            ->add('email_confirmation', 'bot_hidden')
            ->add('submit', 'submit', [
                'label' => 'Utwórz konto',
                'attr' => [
                    'data-submit-state' => 'Rejestracja...'
                ]
            ]);
    }

    public function rules()
    {
        return parent::rules() + ['human' => 'required'];
    }
}
