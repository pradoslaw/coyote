<?php

namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;

class Template implements ParserInterface
{
    const TEMPLATE_REGEXP = "{{Template:(.*?)(\|(.*))*}}";

    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * Template constructor.
     * @param WikiRepository $wiki
     */
    public function __construct(WikiRepository $wiki)
    {
        $this->wiki = $wiki;
    }

    /**
     * @param string $text
     * @return string
     */
    public function parse(string $text): string
    {
        if (!preg_match_all('/' . self::TEMPLATE_REGEXP . '/i', $text, $matches)) {
            return $text;
        }

        for ($i = 0, $count = count($matches[0]); $i < $count; $i++) {
            $path = str_replace(' ', '_', $matches[1][$i]);
            $args = $matches[3][$i] ? explode('|', $matches[3][$i]) : [];

            $wiki = $this->wiki->findByPath($path);

            if ($wiki) {
                foreach ($args as $key => $value) {
                    $wiki->text = str_replace('{{' . ($key + 1) . '}}', $value, $wiki->text);
                }

                $text = str_replace($matches[0][$i], $wiki->text, $text);
            }
        }

        return $text;
    }
}
