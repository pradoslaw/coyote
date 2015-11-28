<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Parser\Scenarios\Microblog as Parser_Microblog;

class ViewController extends Controller
{
    public function index($id, Microblog $repository, User $user)
    {
        $microblog = $repository->findOrFail($id);
        $excerpt = excerpt($microblog->text);

        $this->breadcrumb->push('Mikroblog', route('microblog.home'));
        $this->breadcrumb->push($excerpt, route('microblog.view', [$microblog->id]));

        $microblog->text = (new Parser_Microblog($user))->parse($microblog->text);

        return parent::view('microblog.view')->with(['microblog' => $microblog, 'excerpt' => $excerpt]);
    }
}
