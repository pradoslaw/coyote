<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Http\Factories\CacheFactory;
use Coyote\Repositories\Contracts\BlockRepositoryInterface;
use Coyote\Repositories\Contracts\WikiRepositoryInterface;
use Coyote\Services\Elasticsearch\Factories\Wiki\MoreLikeThisFactory;
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
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            /**
             * Read the block content from database (or cache)
             */
            new Twig_SimpleFunction('render_block', [&$this, 'renderBlock'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('render_help_context', [&$this, 'renderHelpContext'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('render_wiki_mlt', [&$this, 'renderWikiMlt'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('render_wiki_related', [&$this, 'renderWikiRelated'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @param string $name
     * @return mixed|string
     */
    public function renderBlock($name)
    {
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
     * @param \Coyote\Wiki $wiki
     * @return \Illuminate\View\View
     */
    public function renderWikiMlt($wiki)
    {
        $builder = (new MoreLikeThisFactory())->build($wiki);
        $build = $builder->build();

        return view('wiki.partials.mlt', ['mlt' => $this->getWikiRepository()->search($build)->getSource()]);
    }

    /**
     * @param int $wikiId
     * @return \Illuminate\View\View
     */
    public function renderWikiRelated($wikiId)
    {
        return view('wiki.partials.related', ['related' => $this->getWikiRepository()->getRelatedPages($wikiId)]);
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
