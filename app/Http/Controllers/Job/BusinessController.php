<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Plan;
use Coyote\Repositories\Contracts\PlanRepositoryInterface as PlanRepository;

class BusinessController extends Controller
{
    /**
     * @param PlanRepository $plan
     * @return \Illuminate\View\View
     */
    public function show(PlanRepository $plan)
    {
        $plans = $plan->active();

        return $this->view('job.business')->with([
            'plans' => $plans->toJson(),
            'default_plan' => $plans
                ->filter(function (Plan $value) {
                    return $value->is_default === 1;
                })
                ->first()
                ->toJson()
        ]);
    }
}
