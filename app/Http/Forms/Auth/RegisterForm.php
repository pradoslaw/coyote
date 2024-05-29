<?php

namespace Coyote\Http\Forms\Auth;

use Coyote\Domain\User\UserSettings;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;
use Illuminate\Contracts\Validation\Validator;

class RegisterForm extends Form implements ValidatesWhenSubmitted
{
    /** @var string */
    protected $theme = self::THEME_INLINE;

    public function buildForm(): void
    {
        $this
            ->setAttr(['id' => 'js-register-form'])
            ->add('name', 'text', [
                'rules' => 'required|min:2|max:28|username|user_unique',
                'label' => 'Nazwa użytkownika',
                'attr' => ['autofocus' => 'autofocus'],
            ])
            ->add('password', 'password', [
                'rules' => 'required|confirmed|min:3',
                'label' => 'Hasło',
            ])
            ->add('password_confirmation', 'password', [
                'rules' => 'required',
                'label' => 'Hasło (powtórnie)',
            ])
            ->add('email', 'text', [
                'rules' => 'required|email|max:255|email_unique',
                'label' => 'E-mail',
                'help' => 'Nie wysyłamy reklam. Twój e-mail nie zostanie nikomu udostępniony.',
            ])
            ->add('terms', 'checkbox', [
                'rules' => 'accepted',
                'label' => (new UserSettings)->termsAndPrivacyPolicyAgreement(),
            ])
            ->add('marketing_agreement', 'checkbox', [
                'label' => (new UserSettings)->marketingAgreement(),
            ])
            ->add('email_confirmation', 'honeypot')
            ->add('submit', 'submit', [
                'label' => 'Utwórz konto',
                'attr'  => [
                    'class'             => 'g-recaptcha btn btn-primary',
                    'data-sitekey'      => config('services.recaptcha.key'),
                    'data-callback'     => 'onSubmit',
                    'data-submit-state' => 'Rejestracja...',
                ],
            ]);
    }

    public function rules(): array
    {
        return parent::rules() + ['human' => 'required'];
    }

    protected function getValidatorInstance(): Validator
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

    private function makeRequest(string $recaptcha): string
    {
        return \file_get_contents('https://www.google.com/recaptcha/api/siteverify' . '?' . http_build_query([
                'secret'   => config('services.recaptcha.secret'),
                'remoteip' => $this->request->ip(),
                'response' => $recaptcha,
            ]));
    }
}
