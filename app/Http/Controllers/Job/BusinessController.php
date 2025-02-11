<?php
namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Domain\RouteVisits;
use Coyote\Http\Controllers\Controller;
use Coyote\Plan;
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
        $plans = $plan->active();
        return $this->view('job.business', [
            'plans'        => $plans->toJson(),
            'default_plan' => $plans
                ->filter(fn(Plan $value) => $value->is_default === 1)
                ->first()
                ->toJson(),
        ]);
    }
}
