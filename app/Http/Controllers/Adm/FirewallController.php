<?php
namespace Coyote\Http\Controllers\Adm;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Events\FirewallWasDeleted;
use Coyote\Events\FirewallWasSaved;
use Coyote\Firewall;
use Coyote\Http\Forms\FirewallForm;
use Coyote\Http\Grids\Adm\FirewallGrid;
use Coyote\Repositories\Criteria\FirewallList;
use Coyote\Repositories\Eloquent\FirewallRepository;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Firewall as Stream_Firewall;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FirewallController extends BaseController
{
    public function __construct(private FirewallRepository $firewall)
    {
        parent::__construct();
        $this->breadcrumb->push('Bany', route('adm.firewall'));
    }

    public function index(): View
    {
        $this->firewall->pushCriteria(new FirewallList());
        $this->firewall->applyCriteria();
        $grid = $this->gridBuilder()->createGrid(FirewallGrid::class);
        $grid->setSource(new EloquentSource($this->firewall));
        return $this->view('adm.firewall.home', ['grid' => $grid]);
    }

    public function edit(Firewall $firewall): View
    {
        $this->breadcrumb->push('Edycja', route('adm.firewall.save', ['firewall' => $firewall]));
        return $this->view('adm.firewall.save', [
            'form' => $this->createForm(FirewallForm::class, $firewall),
        ]);
    }

    public function save(Firewall $firewall, FirewallForm $form): RedirectResponse
    {
        $firewall->fill($form->all());
        if (empty($firewall->id)) {
            $firewall->moderator_id = $this->userId;
        }
        $this->transaction(function () use ($firewall) {
            $firewall->save();
            $object = (new Stream_Firewall())->map($firewall);
            if ($firewall->wasRecentlyCreated) {
                stream(Stream_Create::class, $object);
            } else {
                stream(Stream_Update::class, $object);
            }
            event(new FirewallWasSaved($firewall));
        });
        return redirect()->route('adm.firewall')->with('success', 'Zapisano poprawnie.');
    }

    public function delete(Firewall $firewall): RedirectResponse
    {
        $this->transaction(function () use ($firewall) {
            $firewall->delete();
            stream(Stream_Delete::class, (new Stream_Firewall())->map($firewall));
            event(new FirewallWasDeleted($firewall));
        });
        return redirect()->route('adm.firewall')->with('success', 'Rekord został poprawnie usunięty.');
    }
}
