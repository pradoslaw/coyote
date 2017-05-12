<?php

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
 * @param $value
 * @param int $limit
 * @return string
 */
function excerpt($value, $limit = 84)
{
    $value = str_replace(["\n", "\t", "\r"], ' ', plain($value));
    $value = trim(preg_replace('/ {2,}/', ' ', $value));

    return str_limit($value, $limit);
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
    $text = preg_replace('/[^a-zA-Z0-9 -]/', '', mb_strtolower(plain($text), 'UTF-8'));
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
        $keywords = array_slice($keywords, 0, $limit);
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
 */
function cdn($path, $secure = null)
{
    if (!config('app.cdn')) {
        return asset($path, $secure);
    }

    $path = trim($path, '/');
    if (in_array(pathinfo($path, PATHINFO_EXTENSION), ['css', 'js'])) {
        $path = elixir($path);
    }

    return ($secure ? 'https:' : '') . '//' . config('app.cdn') . ($path[0] !== '/' ? ('/' . $path) : $path);
}

/**
 * Uppercase first character of each word
 *
 * @param $string
 * @return string
 */
function capitalize($string)
{
    return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
}
