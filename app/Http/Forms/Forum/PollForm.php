<?php

namespace Coyote\Http\Forms\Forum;

use Coyote\Services\FormBuilder\Form;

class PollForm extends Form
{
    const RULE_TITLE           = 'string|max:100';
    // @todo dodac walidator sprawdzajacy ilosc (oraz dlugosc) linii
    const RULE_ITEMS           = 'required_with:title';
    const RULE_MAX_ITEMS       = 'required_with:title|integer|min:1';
    const RULE_LENGTH          = 'required_with:title|integer';

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
                'label' => 'Ilość możliwych odpowiedzi',
                'help' => 'Minimalnie jedna możliwa odpowiedź w ankiecie.',
                'value' => 1
            ])
            ->add('length', 'text', [
                'rules' => self::RULE_LENGTH,
                'label' => 'Długość działania',
                'help' => 'Okreś długość działania ankiety (w dniach). 0 oznacza brak terminu ważności.',
                'value' => 0
            ]);
    }
}
