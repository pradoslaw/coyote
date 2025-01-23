<?php
namespace Coyote\Http\Resources;

use Coyote\Firm;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FirmFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = array_except(parent::toArray($request), ['benefits']);
        /** @var Firm $firm */
        $firm = $this->resource;
        return [
            ...$data,
            'benefits' => $firm->benefits->pluck('name')->toArray(),
            'assets'   => AssetsResource::collection($firm->assets),
            'logo'     => $firm->logo->getFilename() === null
                ? ''
                : url((string)$firm->logo->url()),
        ];
    }
}
