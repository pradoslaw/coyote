<?php
namespace Coyote\Http\Controllers\User;

use Illuminate\View\View;

class TokensController extends BaseController
{
    public function index(): View
    {
        $this->breadcrumb->push('Tokeny API', route('user.tokens'));
        return $this->view('user.tokens');
    }
}
