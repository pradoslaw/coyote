<?php
namespace Neon\View;

readonly class Page
{
    public function __construct(private string $title, private Section $section)
    {
    }

    public function html(callable $h): string
    {
        return '<!DOCTYPE html>' .
            $h('html', [
                $h('head', [
                    '<meta charset="utf-8">',
                    $h('title', [$this->title]),
                ]),
                $h('body', $this->section->html($h)),
            ]);
    }
}
