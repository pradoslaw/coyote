<?php

use Coyote\Services\Helper\DateDifference;

function docker_secret(string $name): Closure
{
    return function () use ($name) {
        $path = '/run/secrets/' . $name;

        return file_exists($path) ? trim(file_get_contents($path)) : null;
    };
}

/**
 * Removes all html tags
 *
 * @param string $value
 * @return string
 */
function plain($value)
{
    return html_entity_decode(strip_tags($value));
}

/**
 * @param null|string $value
 * @param int $limit
 * @return string
 */
function excerpt($value, $limit = 84)
{
    return str_limit(reduced_whitespace(plain($value)), $limit);
}

function reduced_whitespace(string $string): string
{
    return \trim(\preg_replace('/\s+/', ' ', $string));
}

/**
 * Zwraca tablice najczesciej wykorzystywanych slow kluczowych w tekscie
 *
 * @param string $text
 * @param int $limit Limit slow kluczowych
 * @return array
 */
function keywords($text, $limit = 10)
{
    $text = pattern('[^a-zA-Z0-9 -]')->prune(mb_strtolower(plain($text), 'UTF-8'));

    $keywords = [];
    foreach (explode(' ', $text) as $word) {
        if (mb_strlen($word, 'UTF-8') >= 3) {
            $keywords[] = $word;
        }
    }

    $keywords = array_count_values($keywords);
    arsort($keywords);

    $keywords = array_keys($keywords);

    if ($limit) {
        return array_slice($keywords, 0, $limit);
    }

    return $keywords;
}

/**
 * @param \Coyote\Services\Stream\Activities\Activity|string $activity
 * @param \Coyote\Services\Stream\Objects\ObjectInterface|null $object
 * @param \Coyote\Services\Stream\Objects\ObjectInterface|null $target
 */
function stream($activity, $object = null, $target = null)
{
    $manager = app(\Coyote\Services\Stream\Manager::class);

    return $manager->save($activity, $object, $target);
}

/**
 * Creates CDN assets url
 *
 * @param string $path
 * @param null|bool $secure
 * @return string
 * @throws \Exception
 */
function cdn($path, $secure = null)
{
    $path = trim($path, '/');
    $pathinfo = pathinfo($path);

    if (in_array($pathinfo['extension'] ?? '', ['css', 'js'])) {
        $path = manifest(trim($pathinfo['basename'], '/'));
    }

    return asset($path, $secure);
}

/**
 * Get the path to a versioned Mix file.
 *
 * @param string $path
 * @return string
 *
 * @throws \Exception
 */
function manifest($path)
{
    static $manifest;

    if (!$manifest) {
        if (!file_exists($manifestPath = public_path('manifest.json'))) {
            throw new Exception('The webpack manifest does not exist.');
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
    }

    if (!isset($manifest[$path])) {
        throw new Exception(
            "Unable to locate webpack mix file: {$path}. Please check your " .
            'webpack.mix.js output paths and try again.',
        );
    }

    return $manifest[$path];
}

/**
 * @param string|\Carbon\Carbon $dateTime
 * @param bool $diffForHumans
 * @return string
 */
function format_date($dateTime, $diffForHumans = true)
{
    $format = auth()->check() ? auth()->user()->date_format : '%Y-%m-%d %H:%M';

    $diff = new DateDifference($format, $diffForHumans);
    return $diff->format(carbon($dateTime));
}

/**
 * @param $dateTime
 * @return \Carbon\Carbon
 */
function carbon($dateTime)
{
    if (is_null($dateTime)) {
        $dateTime = new \Carbon\Carbon();
    } else if (!$dateTime instanceof \Carbon\Carbon) {
        $dateTime = new \Carbon\Carbon($dateTime);
    }

    return $dateTime;
}
