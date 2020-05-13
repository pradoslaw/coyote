<?php

namespace Coyote\Http\Forms\Auth;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class RegisterForm extends Form implements ValidatesWhenSubmitted
{
    const RECAPTCHA_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var string
     */
    protected $theme = self::THEME_INLINE;

    public function buildForm()
    {
        $this
            ->setAttr(['id' => 'register-form'])
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
            ->add('terms', 'checkbox', [
                'label' => 'Zgadzam się z postanowieniami <a href="/Regulamin">regulaminu</a> oraz <a href="/Polityka_prywatności">polityki prywatności</a>.',
                'rules' => 'accepted'
            ])
            ->add('email_confirmation', 'honeypot')
            ->add('submit', 'submit', [
                'label' => 'Utwórz konto',
                'attr' => [
                    'class' => 'g-recaptcha btn btn-primary',
                    'data-sitekey' => config('services.recaptcha.key'),
                    'data-callback' => 'onSubmit',
                    'data-submit-state' => 'Rejestracja...'
                ]
            ]);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return parent::rules() + ['human' => 'required'];
    }

    /**
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        if (empty(config('services.recaptcha.secret'))) {
            return $validator;
        }

        $validator->after(function ($validator) {
            if (empty($this->request->input('g-recaptcha-response'))) {
                $validator->errors()->add('name', trans('validation.recaptcha'));
                logger()->debug('Empty captcha');

                return false;
            }

            $response = json_decode($this->makeRequest($this->request->input('g-recaptcha-response')), true);
            logger()->debug($response);

            if (!$response['success']) {
                $validator->errors()->add('name', trans('validation.recaptcha'));

                return false;
            }
        });

        return $validator;
    }

    /**
     * @param string $recaptcha
     * @return bool|string
     */
    private function makeRequest(string $recaptcha)
    {
        $data = [
            'secret'    => config('services.recaptcha.secret'),
            'remoteip'  => $this->request->ip(),
            'response'  => $recaptcha
        ];

        return file_get_contents(self::RECAPTCHA_URL . '?' . http_build_query($data));
    }
}
