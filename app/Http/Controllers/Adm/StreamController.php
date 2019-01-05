<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Forms\StreamFilterForm;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as StreamRepository;
use Coyote\Services\Elasticsearch\Builders\Stream\AdmBuilder;
use Coyote\Services\Stream\Renderer;
use Illuminate\Pagination\Paginator;

class StreamController extends BaseController
{
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
        $builder = new AdmBuilder($this->request);
        $result = $this->stream->search($builder);

        $paginator = new Paginator(
            $result->getSource(),
            AdmBuilder::PER_PAGE,
            $this->request->get('page'),
            ['path' => Paginator::resolveCurrentPath()]
        );

        (new Renderer($paginator->items()))->render();
        $paginator->appends($form->getRequest()->except('page'));

        return $this->view('adm.stream', [
            'form' => $form,
            'paginator' => $paginator
        ]);
    }
}
