<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Firm extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws \Exception
     */
    public function toArray($request)
    {
        $data = array_except(parent::toArray($request), ['benefits']);

        return array_merge($data, [
            'thumbnail'     => $this->resource->logo->getFilename() ? (string) $this->resource->logo->url() : cdn('img/logo-gray.png'),
            'logo'          => $this->resource->getOriginal('logo'),
            'benefits'      => $this->resource->benefits->pluck('name')->toArray(),
            'industries'    => $this->resource->industries->pluck('id')->toArray(),
            'gallery'       => $this->gallery($this->resource)
        ]);
    }

    /**
     * @param \Coyote\Firm $firm
     * @return array
     */
    private function gallery($firm)
    {
        $result = [];

        foreach ($firm->gallery as $gallery) {
            $result[] = ['file' => $gallery->file, 'url' => (string) $gallery->photo->url()];
        }

        $result[] = ['file' => '']; // append empty element (always) so user can click on "+" icon to add next one

        return $result;
    }
}
