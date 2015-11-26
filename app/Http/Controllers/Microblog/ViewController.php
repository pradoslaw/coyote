<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;

class ViewController extends Controller
{
    public function index($id, MicroblogRepositoryInterface $repository)
    {
        $microblog = $repository->findOrFail($id);
        $excerpt = excerpt($microblog->text);

        $this->breadcrumb->push('Mikroblog', route('microblog.home'));
        $this->breadcrumb->push($excerpt, route('microblog.view', [$microblog->id]));

        return parent::view('microblog.view')->with(['microblog' => $microblog, 'excerpt' => $excerpt]);
    }
}
