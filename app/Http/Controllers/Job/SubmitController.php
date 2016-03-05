<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Country;
use Coyote\Currency;
use Coyote\Firm\Benefit;
use Coyote\Job;
use Coyote\Http\Controllers\Controller;
use Coyote\Job\Employment;
use Coyote\Job\Rate;

class SubmitController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Praca', route('job.home'));
//        $this->breadcrumb->push('Lorem ipsum', route('job.submit'));

        $countryList = Country::lists('name', 'id');
        $currencyList = Currency::lists('name', 'id');
        $employmentList = Employment::lists('name', 'id');
        $rateList = Rate::lists('name', 'id');

        return $this->view('job.submit.home')->with(compact('countryList', 'currencyList', 'employmentList', 'rateList'));
    }

    public function firm()
    {
        $employeesList = Job::getEmployeesList();
        $foundedList = Job::getFoundedList();
        $benefitsList = Benefit::getBenefitsList();

        return $this->view('job.submit.firm')->with(compact('employeesList', 'foundedList', 'benefitsList'));
    }
}
