<?php

namespace Coyote\Http\Controllers\Pastebin;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\PastebinRepositoryInterface as PastebinRepository;

class SubmitController extends Controller
{
    /**
     * @var PastebinRepository
     */
    protected $pastebin;

    /**
     * @param PastebinRepository $pastebin
     */
    public function __construct(PastebinRepository $pastebin)
    {
        parent::__construct();

        $this->pastebin = $pastebin;
    }
}
