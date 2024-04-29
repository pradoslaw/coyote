<?php
namespace Neon\View\Components\JobOffer;

use Neon\Domain;
use Neon\View\Html\Item;
use Neon\View\Html\Render;
use Neon\View\Html\Tag;
use Neon\View\Theme;

readonly class JobOffersHtml implements Item
{
    public function __construct(
        private string $sectionTitle,
        private array  $offers,
        private Theme  $theme,
    )
    {
    }

    public function render(Render $h): array
    {
        return [
            $h->tag('section', ['id' => 'jobs', 'class' => "mb-8 {$this->theme->jobOffersSection}"], [
                $h->tag('h2',
                    ['class' => "text-xs {$this->theme->jobOffersHeading} mb-4 tracking-tight"],
                    [$this->sectionTitle]),
                $h->tag('div', ['class' => 'space-y-4'], \array_map(
                    fn(JobOffer $offer): Tag => $this->jobOffer($h, $offer),
                    $this->offers,
                )),
            ]),
        ];
    }

    private function jobOffer(Render $h, JobOffer $offer): Tag
    {
        return $h->tag('div', ['class' => 'flex space-x-4'], [
            $h->tag('img', ['src' => $offer->imageUrl, 'class' => 'size-8 shrink-0'], []),
            $h->tag('div', ['class' => 'flex flex-col space-y-1'], [
                $h->tag('h3', ['class' => "font-[Inter] {$this->theme->jobOfferHeading} text-xs font-bold"], [
                    $h->tag('a', ['href' => $offer->url], [$offer->title]),
                ]),
                $h->tag('div', ['class' => 'flex flex-wrap'], [
                    $h->tag('span',
                        ['id' => 'company', 'style' => 'color:#777;', 'class' => 'text-sm whitespace-nowrap mr-4'],
                        [$offer->company]),
                    $h->tag('span', ['class' => 'flex items-center', 'style' => 'color:#777;'], [
                        $this->pinIcon($h, 'h-[14px] w-[11px] mr-1'),
                        $h->tag('div',
                            ['id' => 'cities', 'class' => 'text-sm whitespace-nowrap', 'title' => $offer->citiesTitle],
                            [$offer->citiesSummary]),
                    ]),
                ]),
                $this->jobOfferTags($h, $offer),
            ]),
        ]);
    }

    private function pinIcon(Render $h, string $class): Tag
    {
        return $h->html(<<<pinIcon
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="256" height="256" viewBox="0 0 256 256" xml:space="preserve" class="$class">
                <g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)" >
                    <path d="M 45 0 C 25.463 0 9.625 15.838 9.625 35.375 c 0 8.722 3.171 16.693 8.404 22.861 L 45 90 l 26.97 -31.765 c 5.233 -6.167 8.404 -14.139 8.404 -22.861 C 80.375 15.838 64.537 0 45 0 z M 45 48.705 c -8.035 0 -14.548 -6.513 -14.548 -14.548 c 0 -8.035 6.513 -14.548 14.548 -14.548 s 14.548 6.513 14.548 14.548 C 59.548 42.192 53.035 48.705 45 48.705 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: currentColor; fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
                </g>
            </svg>
            pinIcon,
        );
    }

    private function jobOfferTags(Render $h, JobOffer $offer): Tag
    {
        return $h->tag('div',
            ['id' => 'tags', 'class' => 'flex flex-wrap'],
            \array_map(
                fn(Domain\Tag $tag): Tag => $this->jobOfferTag($h, $tag),
                $offer->tags));
    }

    function jobOfferTag(Render $h, Domain\Tag $tag): Tag
    {
        return $h->tag('span',
            ['class' => "inline-flex shrink-0 mr-1 mb-1 py-px px-1.5 text-xs leading-5 {$this->theme->jobOfferTag} rounded-md font-[Arial] items-center whitespace-nowrap"],
            [
                $tag->imageUrl
                    ? $h->tag('img', ['class' => 'size-3 mr-1', 'src' => $tag->imageUrl], [])
                    : null,
                $tag->name,
            ]);
    }
}
