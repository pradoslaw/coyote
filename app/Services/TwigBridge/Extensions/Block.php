<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Banner;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Repositories\Contracts\BlockRepositoryInterface as BlockRepository;
use Coyote\Repositories\Contracts\CampaignRepositoryInterface as CampaignRepository;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Block as BlockModel;
use Coyote\Campaign as CampaignModel;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Twig_Extension;
use Twig_SimpleFunction;

class Block extends Twig_Extension
{
    use CacheFactory;

    private Filesystem $filesystem;
    private CampaignRepository $campaignRepository;
    private BlockRepository $blockRepository;
    private WikiRepository $wikiRepository;

    public function __construct(Filesystem $filesystem, BlockRepository $blockRepository, CampaignRepository $campaignRepository, WikiRepository $wikiRepository)
    {
        $this->campaignRepository = $campaignRepository;
        $this->blockRepository = $blockRepository;
        $this->filesystem = $filesystem;
        $this->wikiRepository = $wikiRepository;
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
        $blocks = $this->getBlocks()->where('region', $name);
        $html = '';

        foreach ($blocks as $block) {
            $html .= $this->renderBlock($block->name);
        }

        $campaigns = $this->getCampagins()->where('region', $name);

        foreach ($campaigns as $campaign) {
            $html .= $this->renderCampaign($campaign);
        }

        return $html;
    }

    /**
     * @param string $name
     * @return string
     */
    public function renderBlock(string $name)
    {
        $block = $this->getBlocks()->where('name', $name)->first();

        if (!$block || !$block->is_enabled || !$this->shouldDisplayForSponsor($block) || !$this->shouldDisplayForPrivilegeUsers($block)) {
            return '';
        }

        return $block->content;
    }

    private function renderCampaign(CampaignModel $campaign): string
    {
        if (!$campaign->is_enabled || !$this->shouldDisplayForSponsor($campaign) || !$this->shouldDisplayForPrivilegeUsers($campaign)) {
            return '';
        }

        /** @var Banner $banner */
        $banner = $campaign->banners->first();

        if (!$banner) {
            return '';
        }

        $banner->increment('impressions');
        $html = app('html');

        return (string) $html->link(
            route('campaign.redirect', ['banner' => $banner->id]),
            $html->image($this->filesystem->url($banner->filename), null, ['width' => 728, 'height' => 91]),
            ['class' => 'revive', 'target' => '_blank'],
            null,
            false
        );
    }

    private function shouldDisplayForSponsor($block): bool
    {
        if ($block->enable_sponsor || auth()->guest()) {
            return true;
        }

        return !auth()->user()->is_sponsor;
    }

    private function shouldDisplayForPrivilegeUsers($block): bool
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
        $children = $this->wikiRepository->children($helpId);
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
     * @return Collection
     */
    private function getBlocks()
    {
        return $this->getCacheFactory()->rememberForever('blocks', function () {
            return $this->blockRepository->all(['name', 'is_enabled', 'content', 'region', 'max_reputation', 'enable_sponsor']);
        });
    }

    /**
     * @return Collection|\Coyote\Campaign[]
     */
    private function getCampagins()
    {
        return $this->getCacheFactory()->remember('campaigns', now()->hour, function () {
            return $this->campaignRepository->campaigns();
        });
    }
}
