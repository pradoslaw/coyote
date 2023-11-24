<?php
namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Eloquent\WordRepository;
use TRegx\CleanRegex\Pattern;
use TRegx\SafeRegex\Exception\PregException;
use TRegx\SafeRegex\preg;

class Censore extends HashParser implements Parser
{
    public function __construct(private WordRepository $word)
    {
    }

    protected function parseHashed(string $text): string
    {
        static $result;

        if ($result === null) {
            $template = Pattern::template('(?<![\p{L}\p{N}_])@(?![\p{L}\p{N}_])', 'iu');

            foreach ($this->word->allWords() as $word) {
                $pattern = $template->mask($word->word, ['*' => '(\p{L}*?)']);
                $result["$pattern"] = $word->replacement;
            }
        }

        try {
            return preg::replace(array_keys($result), array_values($result), $text);
        } catch (PregException $ignored) {
        }
        return $text;
    }
}
