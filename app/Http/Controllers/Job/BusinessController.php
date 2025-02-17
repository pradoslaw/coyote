<?php
namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Domain\RouteVisits;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Eloquent\PlanRepository;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;

class BusinessController extends Controller
{
    public function show(PlanRepository $plan, RouteVisits $visits): View
    {
        $agent = new Agent();
        if (!$agent->isRobot($this->request->userAgent())) {
            $visits->visit($this->request->path(), Carbon::now()->toDateString());
        }
        return $this->view('job.business', [
            'plans' => $plan->active()->toJson(),
        ]);
    }
}
