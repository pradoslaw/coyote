<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class ElasticsearchResource extends JsonResource
{
    protected const BASE_TIMESTAMP = 946684800;

    /**
     * @return int
     */
    protected function weight(): int
    {
        return 0; // default weight
    }

    /**
     * @return string|null
     */
    protected function getDefaultSuggestTitle(): ?string
    {
        return null;
    }

    /**
     * @return string[]
     */
    protected function categories(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getSuggest(): array
    {
        $result = [];
        $weight = $this->weight();

        foreach ($this->input() as $index => $input) {
            $result[] = [
                'input' => $input,
                'weight' => max(0, $weight - ($index * 100)), // each input has lower weight
                'contexts'  => [
                    'category'     => $this->categories()
                ]
            ];
        }

        return $result;
    }

    /**
     * @return string[]
     */
    protected function input(): array
    {
        $title = htmlspecialchars(trim($this->getDefaultSuggestTitle()));
        $words = preg_split('/\s+/', $title);

        if (count($words) === 1) {
            return [$title];
        }

        $result = [];

        for ($i = 0; $i < 2; $i++) {
            $result[] = implode(' ', array_slice($words, $i));
        }

        return $result;
    }
}
