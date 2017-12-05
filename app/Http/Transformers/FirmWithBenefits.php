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
            'thumbnail'     => $firm->logo->getFilename() ? (string) $firm->logo->url() : cdn('img/logo-gray.png'),
            'logo'          => $firm->getOriginal('logo'),
            'benefits'      => $firm->benefits->pluck('name')->toArray(),
            'industries'    => $firm->industries->pluck('id')->toArray(),
            'gallery'       => $this->gallery($firm)
        ]);
    }

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
