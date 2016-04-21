<?php

namespace TwigBridge\Extensions;

use Coyote\Repositories\Contracts\BlockRepositoryInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class Block extends Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'TwigBridge_Extension_Block';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            /**
             * Read the block content from database (or cache)
             */
            new Twig_SimpleFunction(
                'render_block',
                function ($name) {
                    $cache = app('cache');
                    $content = $cache->get('block:' . $name);

                    if ($content) {
                        return $content;
                    }

                    $block = app(BlockRepositoryInterface::class)->findBy('name', $name);
                    if (!$block) {
                        return '';
                    }

                    if ($block->is_enabled) {
                        if ($block->enable_cache) {
                            $cache->forever('block:' . $name, $block->content);
                        }

                        return $block->content;
                    }
                },
                [
                    'is_safe' => ['html']
                ]
            )
        ];
    }
}
