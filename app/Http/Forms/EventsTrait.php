<?php

namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\FormEvents;
use Coyote\Services\FormBuilder\Form;

trait EventsTrait
{
    protected function transformUserNameToId($field)
    {
        $this->addEventListener(FormEvents::POST_SUBMIT, function (Form $form) use ($field) {
            $username = $form->get($field)->getValue();
            $form->add('user_id', 'hidden');

            if ($username) {
                /** @var \Coyote\User $user */
                $user = $this->repository->findByName($username);

                $form->get('user_id')->setValue($user->id);
            }
        });
    }
}
