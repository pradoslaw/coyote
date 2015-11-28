<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Parser\Parser;
use Coyote\Parser\Providers\Markdown;
use Coyote\Parser\Providers\Purifier;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;

class ViewController extends Controller
{
    public function index($id, MicroblogRepositoryInterface $repository, UserRepositoryInterface $user)
    {
        $microblog = $repository->findOrFail($id);
        $excerpt = excerpt($microblog->text);

        $this->breadcrumb->push('Mikroblog', route('microblog.home'));
        $this->breadcrumb->push($excerpt, route('microblog.view', [$microblog->id]));

        $parser = new Parser();
        $parser->attach((new Markdown($user))->setEnableHashParser(true));
        $parser->attach(new Purifier());

        $microblog->text = $parser->parse($microblog->text);

        return parent::view('microblog.view')->with(['microblog' => $microblog, 'excerpt' => $excerpt]);
    }
}
