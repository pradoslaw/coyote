<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Events\FirewallWasSaved;
use Coyote\Http\Forms\FirewallForm;
use Coyote\Http\Grids\Adm\FirewallGrid;
use Coyote\Repositories\Contracts\FirewallRepositoryInterface as FirewallRepository;
use Coyote\Repositories\Criteria\FirewallList;
use Coyote\Services\Grid\Source\EloquentDataSource;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Firewall as Stream_Firewall;

class FirewallController extends BaseController
{
    /**
     * @var FirewallRepository
     */
    private $firewall;

    /**
     * @param FirewallRepository $firewall
     */
    public function __construct(FirewallRepository $firewall)
    {
        parent::__construct();

        $this->firewall = $firewall;
        $this->breadcrumb->push('Bany', route('adm.firewall'));
    }

    /**
     * @inheritdoc
     */
    public function index()
    {
        $this->firewall->pushCriteria(new FirewallList());
        $this->firewall->applyCriteria();

        $grid = $this->getGrid()->createGrid(FirewallGrid::class);
        $grid->setSource(new EloquentDataSource($this->firewall));

        return $this->view('adm.firewall.home', ['grid' => $grid]);
    }

    /**
     * @param \Coyote\Firewall $firewall
     * @return \Illuminate\View\View
     */
    public function edit($firewall)
    {
        $this->breadcrumb->push('Edycja');
        $form = $this->createForm(FirewallForm::class, $firewall);

        return $this->view('adm.firewall.save', ['form' => $form]);
    }

    /**
     * @param \Coyote\Firewall $firewall
     * @param FirewallForm $form
     * @return mixed
     */
    public function save($firewall, FirewallForm $form)
    {
        $firewall->fill($form->all());
        if (empty($firewall->id)) {
            $firewall->moderator_id = $this->userId;
        }

        $this->transaction(function () use ($firewall) {
            $firewall->save();

            stream(
                $firewall->wasRecentlyCreated ? Stream_Create::class : Stream_Update::class,
                (new Stream_Firewall())->map($firewall)
            );

            event(new FirewallWasSaved($firewall));
        });

        return redirect()->route('adm.firewall')->with('success', 'Zapisano poprawnie.');
    }
}
