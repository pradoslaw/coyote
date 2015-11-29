<?php

namespace Coyote\Parser;

use Coyote\Parser\Providers\ProviderInterface;
use Cache;

final class Parser
{
    private $parsers = [];
    private $enableCache = true;

    public function setEnableCache($flag)
    {
        $this->enableCache = (bool) $flag;
        return $this;
    }

    public function isCacheEnabled()
    {
        return $this->enableCache;
    }

    public function attach(ProviderInterface $parser)
    {
        $this->parsers[] = $parser;
    }

    public function detach($parser)
    {
        //
    }

    public function cache($text, \Closure $closure = null)
    {
        if ($closure) {
            $closure($this);
        }
        $crc32 = 'text:' . hash('crc32b', $text);

        if ($this->enableCache) {
            if (!Cache::has($crc32)) {
                $text = $this->parse($text);
                Cache::put($crc32, $text, 60 * 24 * 30); // 30d
            } else {
                $text = Cache::get($crc32);
                Cache::increment($crc32, 60 * 24);
            }
        } else {
            $text = $this->parse($text);
        }

        $this->parsers = [];
        return $text;
    }

    public function parse($text)
    {
        foreach ($this->parsers as $parser) {
            $text = $parser->parse($text);
        }

        return $text;
    }
}
