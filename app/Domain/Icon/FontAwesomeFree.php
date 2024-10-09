<?php
namespace Coyote\Domain\Icon;

readonly class FontAwesomeFree
{
    public function icons(): array
    {
        return [
            'adminTickMark'  => 'fa-solid fa-check',
            'adminCrossMark' => 'fa-solid fa-xmark',
        ];
    }
}
