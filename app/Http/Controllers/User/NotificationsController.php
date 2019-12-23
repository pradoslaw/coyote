<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Notification;
use Coyote\Repositories\Contracts\NotificationRepositoryInterface as NotificationRepository;
use Coyote\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Carbon;

class NotificationsController extends BaseController
{
    use SettingsTrait, HomeTrait {
        SettingsTrait::getSideMenu as settingsSideMenu;
        HomeTrait::getSideMenu as homeSideMenu;
    }

    /**
     * @var NotificationRepository
     */
    private $notification;

    /**
     * @param NotificationRepository $notification
     */
    public function __construct(NotificationRepository $notification)
    {
        parent::__construct();

        $this->notification = $notification;
    }

    /**
     * @return mixed
     */
    public function getSideMenu()
    {
        if ($this->request->route()->getName() == 'user.notifications') {
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
        $this->breadcrumb->push('Powiadomienia', route('user.notifications'));

        $pagination = $this->notification->lengthAwarePaginate($this->userId);
        // mark as read
        $this->mark($pagination);

        $pagination->setCollection(
            collect(NotificationResource::collection($pagination->getCollection())->toArray($this->request))
        );

        return $this->view('user.notifications.home', [
            'pagination'          => $pagination
        ]);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $this->breadcrumb->push('Ustawienia powiadomień', route('user.notifications.settings'));
        $groups = $this->notification->getUserSettings($this->userId)->groupBy('category');

        return $this->view('user.notifications.settings', compact('groups'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $this->notification->setUserSettings($this->userId, $request->input('settings'));

        return back()->with('success', 'Zmiany zostały zapisane');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax(Request $request)
    {
        $unread = $this->auth->notifications_unread;
        $offset = $request->query('offset', 0);

        $notifications = $this->notification->takeForUser($this->userId, max(10, $unread), $offset);
        $unread -= $this->mark($notifications);

        // format notification's headline
        $notifications = array_filter(NotificationResource::collection($notifications)->toArray($this->request));

        return response()->json([
            'count'             => $unread,
            'notifications'     => $notifications
        ]);
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        $this->notification->delete($id);
    }

    /**
     * Marks all alerts as read
     */
    public function markAsRead()
    {
        if ($this->auth->notifications_unread) {
            $this->notification->where('user_id', $this->userId)->whereNull('read_at')->update([
                'read_at' => Carbon\Carbon::now()
            ]);
        }

        $this->notification->where('user_id', $this->userId)->update(['is_clicked' => true]);
    }

    /**
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function url(string $id)
    {
        /** @var \Coyote\Notification $notification */
        $notification = $this->notification->find($id, ['id', 'url', 'read_at', 'is_clicked']);
        abort_if($notification === null, 404);

        $notification->is_clicked = true;

        if (!$notification->read_at) {
            $notification->read_at = Carbon\Carbon::now();
        }

        $notification->save();

        return redirect()->to($notification->url);
    }

    /**
     * Mark alerts as read and returns number of marked alerts
     *
     * @param \Illuminate\Support\Collection $notifications
     * @return int
     */
    private function mark($notifications)
    {
        $ids = $notifications
            ->reject(function (Notification $notification) {
                return $notification->read_at !== null;
            })
            ->pluck('id')
            ->all();

        if (!empty($ids)) {
            $this->notification->markAsRead($ids);
        }

        return count($ids);
    }
}
