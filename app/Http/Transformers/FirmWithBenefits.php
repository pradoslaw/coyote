<?php

namespace Coyote\Http\Transformers;

use Coyote\Firm;
use League\Fractal\TransformerAbstract;

class FirmWithBenefits extends TransformerAbstract
{
    /**
     * @param Firm $firm
     * @return array
     */
    public function transform(Firm $firm): array
    {
        $data = array_except($firm->toArray(), ['benefits']);

        return array_merge($data, [
            'logo' => $firm->logo->getFilename() ? (string) $firm->logo->url() : cdn('img/logo-gray.png'),
            'benefits' => $firm->benefits->pluck('name')->toArray()
        ]);
    }
}
