<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Alert;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as AlertRepository;
use Coyote\Transformers\AlertTransformer;
use Illuminate\Http\Request;
use Carbon;

class AlertsController extends BaseController
{
    use SettingsTrait, HomeTrait {
        SettingsTrait::getSideMenu as settingsSideMenu;
        HomeTrait::getSideMenu as homeSideMenu;
    }

    /**
     * @var AlertRepository
     */
    private $alert;

    /**
     * @param AlertRepository $alert
     */
    public function __construct(AlertRepository $alert)
    {
        parent::__construct();

        $this->alert = $alert;
    }

    /**
     * @return mixed
     */
    public function getSideMenu()
    {
        if ($this->request->route()->getName() == 'user.alerts') {
            return $this->homeSideMenu();
        } else {
            return $this->settingsSideMenu();
        }
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Powiadomienia', route('user.alerts'));

        $pagination = $this->alert->paginate($this->userId);
        // mark as read
        $this->mark($pagination);

        $pagination->setCollection(collect(fractal($pagination->getCollection(), new AlertTransformer())->toArray()));

        return $this->view('user.alerts.home', [
            'pagination'          => $pagination,
            'session_created_at'  => $this->request->session()->get('created_at')
        ]);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $this->breadcrumb->push('Ustawienia powiadomień', route('user.alerts.settings'));
        $groups = $this->alert->getUserSettings($this->userId)->groupBy('category');

        return $this->view('user.alerts.settings', compact('groups'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $this->alert->setUserSettings($this->userId, $request->input('settings'));

        return back()->with('success', 'Zmiany zostały zapisane');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax(Request $request)
    {
        $unread = $this->auth->alerts_unread;

        $alerts = $this->alert->takeForUser($this->userId, max(10, $unread), $request->query('offset', 0));
        $unread -= $this->mark($alerts);

        // format notification's headline
        $alerts = collect(fractal($alerts, new AlertTransformer())->toArray());

        $view = view('user.alerts.ajax', [
            'alerts'               => $alerts,
            'session_created_at'   => $this->request->session()->get('created_at')
        ]);

        return response()->json([
            'html'      => $view->render(),
            'unread'    => $unread
        ]);
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        $this->alert->delete($id);
    }

    /**
     * Marks all alerts as read
     */
    public function markAsRead()
    {
        if ($this->auth->alerts_unread) {
            $this->alert->where('user_id', $this->userId)->whereNull('read_at')->update([
                'read_at' => Carbon\Carbon::now()
            ]);
        }

        $this->alert->where('user_id', $this->userId)->update(['is_marked' => true]);
    }

    /**
     * @param string $guid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function url(string $guid)
    {
        /** @var \Coyote\Alert $alert */
        $alert = $this->alert->findBy('guid', $guid, ['id', 'url', 'read_at', 'is_marked']);
        abort_if($alert === null, 404);

        $alert->is_marked = true;

        if (!$alert->read_at) {
            $alert->read_at = Carbon\Carbon::now();
        }

        $alert->save();

        return redirect()->to($alert->url);
    }

    /**
     * Mark alerts as read and returns number of marked alerts
     *
     * @param \Illuminate\Support\Collection $alerts
     * @return int
     */
    private function mark($alerts)
    {
        $ids = $alerts
            ->reject(function (Alert $alert) {
                return $alert->read_at !== null;
            })
            ->pluck('id')
            ->all();

        if (!empty($ids)) {
            $this->alert->markAsRead($ids);
        }

        return count($ids);
    }
}
