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
                    'name'           => 'Standard',
                    'price'          => 39,
                    'durationInDays' => 40,
                    'createOfferUrl' => $this->createOfferUrl('Standard'),
                ],
                [
                    'name'           => 'Plus',
                    'price'          => 65,
                    'durationInDays' => 40,
                    'bulletPoints'   => [
                        'Promocja ogłoszenia w kanałach social media',
                        new StringHtml('Podbicie ogłoszenia <strong>1x</strong>'),
                        new StringHtml('<strong>Reklama</strong> oferty na forum i stronie głównej'),
                    ],
                    'createOfferUrl' => $this->createOfferUrl('Plus'),
                ],
                [
                    'name'           => 'Premium',
                    'price'          => 159,
                    'durationInDays' => 40,
                    'bulletPoints'   => [
                        new StringHtml('Pakiet zawiera <strong>wszystkie punkty</strong> z planu Plus'),
                        new StringHtml('Podbicie ogłoszenia <strong>3x</strong>'),
                        new StringHtml('<strong>Wyróżnienie</strong> kolorem'),
                        new StringHtml('Wyróżnienie ogłoszenia <strong>na górze listy</strong> wyszukiwania'),
                    ],
                    'createOfferUrl' => $this->createOfferUrl('Premium'),
                ],
            ],
        ]);
    }

    private function createOfferUrl(string $planName): string
    {
        return route('job.submit', ['default_plan' => $this->planIdByName($planName)]);
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
