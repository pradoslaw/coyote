<?php

namespace Coyote\Http\Resources;

use Coyote\Firm;
use Illuminate\Http\Resources\Json\JsonResource;

class FirmFormResource extends JsonResource
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
            'benefits'      => $this->resource->benefits->pluck('name')->toArray(),
            'gallery'       => $this->gallery($this->resource),
            'logo'          => (string) $this->resource->logo->url()
        ]);
    }

    private function gallery(Firm $firm): array
    {
        return $firm
            ->gallery
            ->map(function ($gallery) {
                return (string) $gallery->photo->url();
            })
            ->push('')
            ->toArray();
    }
}
