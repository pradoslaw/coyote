<?php
namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Domain\RouteVisits;
use Coyote\Domain\StringHtml;
use Coyote\Http\Controllers\Controller;
use Coyote\Plan;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;

class BusinessController extends Controller
{
    public function show(RouteVisits $visits): View
    {
        $agent = new Agent();
        if (!$agent->isRobot($this->request->userAgent())) {
            $visits->visit($this->request->path(), Carbon::now()->toDateString());
        }
        return $this->view('job.business', [
            'plans' => [
                [
                    'name'           => 'Free',
                    'price'          => 0,
                    'durationInDays' => 14,
                    'bulletPoints'   => [
                        'Za pierwsze ogłoszenie każdego miesiąca',
                        'Tylko dla organizacji pożytku publicznego, uczelni wyższych oraz firm zatrudniających do 5 osób.',
                    ],
                    'createOfferUrl' => $this->createOfferUrl('Free'),
                    'bundleSize'     => 1,
                ],
                [
                    'name'           => 'Premium',
                    'price'          => 159,
                    'durationInDays' => 30,
                    'bulletPoints'   => [
                        new StringHtml('Do wykorzystania w ciągu <strong>12 miesięcy</strong>'),
                        new StringHtml('Gwarancja przedstawienia oferty min. <strong>1000 programistom</strong> lub zwrot pieniędzy'),
                        new StringHtml('Gwarancja min. <strong>10,000 wyświetleń</strong> wizytówki Twojej oferty na portalu 4programmers'),
                        new StringHtml('Automatyczne <strong>3 podbicia</strong>'),
                        new StringHtml('Do <strong>10 lokalizacji</strong>'),
                    ],
                    'createOfferUrl' => $this->createOfferUrl('Premium'),
                    'bundleSize'     => 1,
                ],
                [
                    'name'           => 'Strategic',
                    'price'          => 119,
                    'fullPrice'      => 357,
                    'durationInDays' => 30,
                    'bulletPoints'   => [
                        new StringHtml('Pakiet <strong>zawiera</strong> wszystkie punkty z planu Premium'),
                    ],
                    'discount'       => '25%',
                    'createOfferUrl' => $this->createOfferUrl('Strategic'),
                    'bundleSize'     => 3,
                ],
                [
                    'name'           => 'Growth',
                    'price'          => 99,
                    'fullPrice'      => 495,
                    'durationInDays' => 30,
                    'bulletPoints'   => [
                        new StringHtml('Pakiet <strong>zawiera</strong> wszystkie punkty z planu Premium'),
                    ],
                    'discount'       => '38%',
                    'createOfferUrl' => $this->createOfferUrl('Growth'),

                    'bundleSize' => 5,
                ],
                [
                    'name'           => 'Scale',
                    'fullPrice'      => 1580,
                    'price'          => 79,
                    'durationInDays' => 30,
                    'bulletPoints'   => [
                        new StringHtml('Pakiet <strong>zawiera</strong> wszystkie punkty z planu Premium'),
                    ],
                    'discount'       => '50%',
                    'createOfferUrl' => $this->createOfferUrl('Scale'),
                    'bundleSize'     => 20,
                ],
            ],
        ]);
    }

    private function createOfferUrl(string $planName): string
    {
        return route('job.submit', ['plan' => $this->planIdByName($planName)]);
    }

    private function planIdByName(string $planName): int
    {
        return Plan::query()
            ->where('name', $planName)
            ->where('is_active', true)
            ->firstOrFail('id')
            ->id;
    }
}
