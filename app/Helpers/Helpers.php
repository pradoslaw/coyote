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
 * @param \Coyote\Services\Stream\Activities\Activity|null $activity
 * @param \Coyote\Services\Stream\Objects\ObjectInterface|null $object
 * @param \Coyote\Services\Stream\Objects\ObjectInterface|null $target
 */
function stream($activity = null, $object = null, $target = null)
{
    $stream = app(\Coyote\Repositories\Contracts\StreamRepositoryInterface::class);

    if ($activity) {
        if (is_string($activity)) {
            $actor = new Coyote\Services\Stream\Actor(auth()->user());

            $class = 'Coyote\\Services\\Stream\\Activities\\' . ucfirst(camel_case(class_basename($activity)));
            $stream->create((new $class($actor, $object, $target))->build());
        } else {
            if ($object !== null) {
                $activity->setObject($object);
            }
            if ($target !== null) {
                $activity->setTarget($target);
            }

            $stream->create($activity->build());
        }
    }
}

/**
 * Creates CDN assets url
 *
 * @param string $path
 * @param null $secure
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

    return '//' . config('app.cdn') . ($path[0] !== '/' ? ('/' . $path) : $path);
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
