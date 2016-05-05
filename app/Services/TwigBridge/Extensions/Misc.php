<?php

namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Declination;
use Coyote\Services\Media\MediaInterface;
use Coyote\Services\Thumbnail\Objects\Microblog;
use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleFilter;

class Misc extends Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'TwigBridge_Extension_Misc';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('timer', [$this, 'getGenerationTime']),
            new Twig_SimpleFunction('declination', [Declination::class, 'format']),
            new Twig_SimpleFunction('sortable', [$this, 'sortable'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('thumbnail', [$this, 'thumbnail'])
        ];
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('encrypt', function ($data) {
                return app('encrypter')->encrypt($data);
            }),
        ];
    }

    /**
     * Zwraca czas generowania strony w sekundach lub milisekundach
     *
     * @return string
     */
    public function getGenerationTime()
    {
        // w przypadku testow funkcjonalnych, stala ta nie jest deklarowana
        if (!defined('LARAVEL_START')) {
            return false;
        }

        $timer = microtime(true) - LARAVEL_START;

        if ($timer < 1) {
            return (int) substr((string) $timer, 2, 3) . ' ms';
        } else {
            return number_format($timer, 2) . ' s';
        }
    }

    /**
     * Generate thumbnail URL for microblog attachments...
     *
     * @param MediaInterface $media
     * @return string
     */
    public function thumbnail(MediaInterface $media)
    {
        // @todo obecnie generuje miniatury tylko dla mikroblogow. moze warto to rozszerzyc?
        return $media->getFactory()->getThumbnail()->url(new Microblog())->make($media->url());
    }

    /**
     * @return string
     */
    public function sortable()
    {
        $args = func_get_args();

        $column = array_shift($args);
        $title = array_shift($args);
        $default = array_shift($args);

        $sort = request('sort', $default[0]);
        $order = request('order', $default[1]);

        $parameters = array_merge(
            request()->all(),
            ['sort' => $column, 'order' => $order == 'desc' ? 'asc' : 'desc']
        );

        return link_to(
            request()->path() . '?' . http_build_query($parameters),
            $title,
            ['class' => "sort " . ($sort == $column ? strtolower($order) : '')]
        );
    }
}
