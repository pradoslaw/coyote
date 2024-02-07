<?php
namespace Coyote\Services\TwigBridge\Extensions;

use Coyote\Domain\Clock;
use Coyote\Services\Declination;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class Misc extends Twig_Extension
{
    public function __construct(private Clock $clock)
    {
    }

    public function getName(): string
    {
        return 'TwigBridge_Extension_Misc';
    }

    public function getFunctions(): array
    {
        return [
            new Twig_SimpleFunction('timer', [$this, 'totalRuntime']),
            new Twig_SimpleFunction('github', [$this, 'githubAccountName']),
            new Twig_SimpleFunction('declination', [Declination::class, 'format']),
            new Twig_SimpleFunction('sortable', [$this, 'sortable'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('is_url', [$this, 'isUrl'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('current_url', [$this, 'currentUrl']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new Twig_SimpleFilter('encrypt', function ($data) {
                return app('encrypter')->encrypt($data);
            }),
            // uzywane w szablonie home.twig do poprawnego wyswietlania title w sekcji "ostatnie zmiany na forum"
            new Twig_SimpleFilter('unescape', function ($value) {
                return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
            }),
        ];
    }

    public function totalRuntime(): string
    {
        $timer = $this->clock->executionTime();
        if ($timer < 1) {
            return (int)substr((string)$timer, 2, 3) . ' ms';
        }
        return number_format($timer, 2) . ' s';
    }

    /**
     * @param string $url
     * @return string
     */
    public function githubAccountName($url)
    {
        if (!$url) {
            return '';
        }
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return '';
        }
        return trim($path, '/');
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
            ['sort' => $column, 'order' => $order == 'desc' ? 'asc' : 'desc'],
        );

        return link_to(
            request()->path() . '?' . http_build_query($parameters),
            $title,
            ['class' => "sort " . ($sort == $column ? strtolower($order) : '')],
        );
    }

    /**
     * @param string $url
     * @return bool
     */
    public function isUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * @return string
     */
    public function currentUrl(): string
    {
        return request()->fullUrl();
    }
}
