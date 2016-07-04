<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Http\Factories\CacheFactory;
use Coyote\Repositories\Contracts\BlockRepositoryInterface;
use Coyote\Repositories\Contracts\WikiRepositoryInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class Block extends Twig_Extension
{
    use CacheFactory;

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
                    $cache = $this->getCacheFactory();
                    $content = $cache->get('block:' . $name);

                    if ($content) {
                        return $content;
                    }

                    $block = $this->getBlockRepository()->findBy('name', $name);
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
            ),

            new Twig_SimpleFunction('render_help_context', [&$this, 'renderHelpContext'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @param string $helpId
     * @param \Coyote\Wiki $wiki
     * @return string
     */
    public function renderHelpContext($helpId, $wiki)
    {
        $children = $this->getWikiRepository()->children($helpId);
        $html = '<ul>';

        foreach ($children as $idx => $row) {
            $depth = $row['depth'];
            $nextDepth = isset($children[$idx + 1]) ? $children[$idx + 1]['depth'] : 1;

            $link = link_to($row['path'], $row['title'], $row['id'] == $wiki->id ? ['class' => 'active'] : []);

            if ($nextDepth > $depth) {
                $html .= '<li>' . $link . "<ul>";
            } else {
                $html .= '<li>' . $link . "</li>";
            }

            if ($nextDepth < $depth) {
                while ($nextDepth < $depth) {
                    $html .= "\n</ul></li>";
                    --$depth;
                }
            }
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * @return BlockRepositoryInterface
     */
    private function getBlockRepository()
    {
        return app(BlockRepositoryInterface::class);
    }

    /**
     * @return WikiRepositoryInterface
     */
    private function getWikiRepository()
    {
        return app(WikiRepositoryInterface::class);
    }
}
