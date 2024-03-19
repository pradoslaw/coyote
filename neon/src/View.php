<?php
namespace Neon;

class View
{
    public function __construct(readonly private string $title)
    {
    }

    public function html(): string
    {
        return '<!DOCTYPE html>' .
            '<html>' .
            '<title>' . $this->title . '</title>' .
            '</html>';
    }
}
