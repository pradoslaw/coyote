<?php

namespace Coyote\Services\Parser\Factories;

class DiffFactory extends AbstractFactory
{
    /**
     * @var string
     */
    protected $context;

    /**
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Parse post
     *
     * @param string $text
     * @return string
     */
    public function parse(string $text) : string
    {
        start_measure('parsing', 'Making diff...');

        if ($this->context) {
            $diff = new \Diff($this->explode($text), $this->explode($this->context));
            $renderer = new \Diff_Renderer_Text_Unified;
            $text = nl2br($diff->render($renderer));
        }

        stop_measure('parsing');

        return $text;
    }

    /**
     * @param string $text
     * @return array
     */
    private function explode($text)
    {
        return explode("\n", $text);
    }
}
