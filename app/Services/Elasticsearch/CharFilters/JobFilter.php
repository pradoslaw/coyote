<?php

namespace Coyote\Services\Elasticsearch\CharFilters;

use Coyote\Services\Parser\Factories\JobFactory as Parser;

class JobFilter extends CharFilter
{
    /**
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param array $data
     * @return array
     */
    public function filter(array $data): array
    {
        $data['title'] = htmlspecialchars($data['title']);
        $data['description'] = $this->stripHtml($data['description']);

        if (!empty($data['requirements'])) {
            $data['requirements'] = $this->stripHtml($data['requirements']);
        }

        if (!empty($data['recruitment'])) {
            $data['recruitment'] = $this->stripHtml($data['recruitment']);
        }

        return $data;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function stripHtml($value)
    {
        // w oferach pracy, edytor tinymce nie dodaje znaku nowej linii. zamiast tego mamy <br />. zamieniamy
        // na znak nowej linii aby poprawnie zindeksowac tekst w elasticsearch. w przeciwnym przypadku
        // teks foo<br />bar po przepuszczeniu przez stripHtml() zostalby zamieniony na foobar co niepoprawnie
        // zostaloby zindeksowane jako jeden wyraz
        return parent::stripHtml(str_replace(['<br />', '<br>'], "\n", $value));
    }
}
