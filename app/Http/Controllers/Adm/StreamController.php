<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Factories\StreamFactory;
use Coyote\Http\Forms\Adm\StreamFilterForm;
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
        $this->breadcrumb->push('Dziennik zdarzeń');
    }

    /**
     * @inheritdoc
     */
    public function index()
    {
        return $this->view('adm.stream', [
            'form' => $this->getForm()
        ]);
    }

    /**
     * @param StreamFilterForm $form
     * @return \Illuminate\View\View
     */
    public function filter(StreamFilterForm $form)
    {
        $paginator = $this->stream->filter($form);
        $this->getStreamFactory()->decorate($paginator->items());

        return $this->view('adm.stream', [
            'form' => $form,
            'paginator' => $paginator
        ]);
    }

    /**
     * @return \Coyote\Services\FormBuilder\Form
     */
    private function getForm()
    {
        return $this->createForm(StreamFilterForm::class, null, [
            'url' => route('adm.stream.filter')
        ]);
    }
}
