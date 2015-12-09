<?php

/**
 * @param $value
 * @param int $limit
 * @param string $end
 * @return string
 */
function html_limit($value, $limit = 100, $end = '...')
{
    $value = htmlspecialchars_decode($value);
    $value = str_limit($value, $limit, $end);

    return htmlspecialchars($value);
}

/**
 * Removes all html tags and converts entities to their applicable characters
 *
 * @param string $value
 * @return string
 */
function plain($value, $stripTags = true)
{
    if ($stripTags) {
        $value = strip_tags($value);
    }

    return html_entity_decode($value, ENT_COMPAT | ENT_HTML401, 'UTF-8');
}

/**
 * @param $value
 * @param int $limit
 * @return string
 */
function excerpt($value, $limit = 64)
{
    $value = str_replace(["\n", "\t", "\r"], ' ', plain($value));
    $value = trim(preg_replace('/ {2,}/', ' ', $value));

    $value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);

    return html_limit(plain($value), $limit);
}
