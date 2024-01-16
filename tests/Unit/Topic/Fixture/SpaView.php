<?php
namespace Tests\Unit\Topic\Fixture;

use Tests\Unit\BaseFixture\ViewFixture;

class SpaView
{
    private ViewFixture $view;

    public function __construct(private string $html)
    {
        $this->view = new ViewFixture($this->html);
    }

    public function jsVariables(): array
    {
        $variables = [];
        foreach ($this->javaScriptVariableDeclarations() as $declaration) {
            \preg_match('/var\s+(\w+)\s+=\s+(.+);/', $declaration, $match);
            if ($match) {
                $variables[$match[1]] = \json_decode($match[2], true);
            }
        }
        return $variables;
    }

    private function javaScriptVariableDeclarations(): \Iterator
    {
        foreach ($this->view->javaScriptDeclarations() as $script) {
            if ($script->type() === 'application/ld+json') {
                continue;
            }
            foreach (explode("\n", $script->content()) as $line) {
                if ($line === '') {
                    continue;
                }
                yield \trim($line);
            }
        }
    }
}
