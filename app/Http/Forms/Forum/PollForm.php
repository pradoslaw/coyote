<?php

namespace Coyote\Http\Forms\Forum;

use Coyote\Services\FormBuilder\Form;

class PollForm extends Form
{
    const RULE_TITLE           = 'nullable|string|max:100';
    // @todo dodac walidator sprawdzajacy liczbe (oraz dlugosc) linii
    // @todo musimy pisac "poll.title" jezeli ten formularz jest dzieckiem. reguly powinny byc zmieniane
    // przez klase Form
    const RULE_ITEMS           = 'required_with:poll.title';
    const RULE_MAX_ITEMS       = 'required_with:poll.title|integer|min:1';
    const RULE_LENGTH          = 'required_with:poll.title|integer';

    public function buildForm()
    {
        $this
            ->add('title', 'text', [
                'rules' => self::RULE_TITLE,
                'label' => 'Tytuł ankiety'
            ])
            ->add('items', 'textarea', [
                'rules' => self::RULE_ITEMS,
                'label' => 'Odpowiedzi w ankiecie'
            ])
            ->add('max_items', 'text', [
                'rules' => self::RULE_MAX_ITEMS,
                'label' => 'Liczba możliwych odpowiedzi',
                'help' => 'Minimalnie jedna możliwa odpowiedź w ankiecie.'
            ])
            ->add('length', 'text', [
                'rules' => self::RULE_LENGTH,
                'label' => 'Długość działania',
                'help' => 'Okreś długość działania ankiety (w dniach). 0 oznacza brak terminu ważności.'
            ]);

        if (!empty($this->data->id)) {
            $this->add('remove', 'checkbox', [
                'label' => 'Usuń ankietę'
            ]);
        }
    }
}
