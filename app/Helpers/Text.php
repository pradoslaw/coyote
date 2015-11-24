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
 * Metoda "oczyszcza" tekst ze znacznikow HTML, znakowej nowej linii czy zbednych spacji
 *
 * @param string $value
 * @return string
 */
function plain($value, $stripTags = true)
{
    if ($stripTags) {
        $value = strip_tags($value);
    }
    $value = str_replace(["\n", "\t", "\r"], ' ', $value);
    $value = trim(preg_replace('/ {2,}/', ' ', htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false)));

    return $value;
}

/**
 * @param $value
 * @param int $limit
 * @return string
 */
function excerpt($value, $limit = 64)
{
    return html_limit(plain($value), $limit);
}
