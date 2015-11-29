<?php

namespace Coyote\Parser;

use Coyote\Parser\Providers\ProviderInterface;
use Cache;
use Debugbar;

final class Parser
{
    private $parsers = [];
    private $enableCache = false;

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
        $crc32 = hash('crc32b', $text);

        if ($this->enableCache) {
            if (!Cache::has($crc32)) {
                $text = $this->parse($text);
                Cache::forever($crc32, $text);
            } else {
                $text = Cache::get($crc32);
            }
        } else {
            $text = $this->parse($text);
        }

        $this->parsers = [];
        return $text;
    }

    public function parse($text)
    {
        Debugbar::startMeasure('parsing', 'Time for parsing');

        foreach ($this->parsers as $parser) {
            $text = $parser->parse($text);
        }

        Debugbar::stopMeasure('parsing');
        return $text;
    }
}
