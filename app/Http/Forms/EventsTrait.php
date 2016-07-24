<?php

namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\FormEvents;
use Coyote\Services\FormBuilder\Form;

trait EventsTrait
{
    protected function transformUserNameToId($from, $to = 'user_id')
    {
        $this->addEventListener(FormEvents::POST_SUBMIT, function (Form $form) use ($from, $to) {
            $username = $form->get($from)->getValue();
            $form->add($to, 'hidden');

            if ($username) {
                /** @var \Coyote\User $user */
                $user = $this->repository->findByName($username);

                $form->get($to)->setValue($user->id);
            }
        });
    }
}
