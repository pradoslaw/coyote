<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Factories\StreamFactory;
use Coyote\Http\Forms\StreamFilterForm;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as StreamRepository;

class StreamController extends BaseController
{
    use StreamFactory;

    /**
     * @var StreamRepository
     */
    protected $stream;

    /**
     * @param StreamRepository $stream
     */
    public function __construct(StreamRepository $stream)
    {
        parent::__construct();

        $this->stream = $stream;
        $this->breadcrumb->push('Dziennik zdarzeń', route('adm.stream'));
    }

    /**
     * @param StreamFilterForm $form
     * @return \Illuminate\View\View
     */
    public function index(StreamFilterForm $form)
    {
        $paginator = $this->stream->filter($form);
        $this->getStreamFactory()->decorate($paginator->items());

        $paginator->appends($form->getRequest()->except('page'));

        return $this->view('adm.stream', [
            'form' => $form,
            'paginator' => $paginator
        ]);
    }
}
