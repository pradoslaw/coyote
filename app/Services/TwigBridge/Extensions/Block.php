<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Http\Factories\CacheFactory;
use Coyote\Repositories\Contracts\BlockRepositoryInterface;
use Coyote\Repositories\Contracts\WikiRepositoryInterface;
use Coyote\Block as Model;
use Twig_Extension;
use Twig_SimpleFunction;

class Block extends Twig_Extension
{
    use CacheFactory;

    /**
     * @var \Illuminate\Support\Collection
     */
    public $blocks;

    public function __construct()
    {
        $this->blocks = $this->getBlocks();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'TwigBridge_Extension_Block';
    }

    /**
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            /**
             * Read the block content from database (or cache)
             */
            new Twig_SimpleFunction('render_region', [&$this, 'renderRegion'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('render_block', [&$this, 'renderBlock'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('render_help_context', [&$this, 'renderHelpContext'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @param string $name
     * @return string
     */
    public function renderRegion($name)
    {
        /** @var \Coyote\Block $block */
        $blocks = $this->blocks->where('region', $name);
        $html = '';

        foreach ($blocks as $block) {
            $html .= $this->renderBlock($block->name);
        }

        return $html;
    }

    /**
     * @param string $name
     * @return string
     */
    public function renderBlock($name)
    {
        /** @var \Coyote\Block $block */
        $block = $this->blocks->where('name', $name)->first();

        if (!$block || !$block->is_enabled || !$this->shouldDisplayForSponsor($block) || !$this->shouldDisplayForPrivilegeUsers($block)) {
            return '';
        }

        return $block->content;
    }

    private function shouldDisplayForSponsor(Model $block): bool
    {
        if ($block->enable_sponsor || auth()->guest()) {
            return true;
        }

        return !auth()->user()->is_sponsor;
    }

    private function shouldDisplayForPrivilegeUsers(Model $block): bool
    {
        if (!$block->max_reputation || auth()->guest()) {
            return true;
        }

        return auth()->user()->reputation < $block->max_reputation;
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

    /**
     * @return \Coyote\Block[]
     */
    private function getBlocks()
    {
        return $this->getCacheFactory()->rememberForever('blocks', function () {
            return $this->getBlockRepository()->all(['name', 'is_enabled', 'content', 'region', 'max_reputation', 'enable_sponsor']);
        });
    }
}
