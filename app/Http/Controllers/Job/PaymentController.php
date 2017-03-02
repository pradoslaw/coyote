<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Job\PaymentForm;

class PaymentController extends Controller
{
    public function index()
    {
        $this->breadcrumb->push('Praca', route('job.home'));
        $this->breadcrumb->push('Promowanie ogłoszenia');

        return $this->view('job.payment', [
            'form' => $this->createForm(PaymentForm::class)
        ]);
    }

    public function process()
    {

    }
}
