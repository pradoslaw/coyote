<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\NotificationRead;
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

    public function __construct(private NotificationRepository $notification)
    {
        parent::__construct();
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

        return $this->view('user.notifications.settings', [
            'groups'        => $this->notification->notificationTypes()->groupBy('category'),
            'settings'      => $this->auth->notificationSettings()->get()->sortBy('channel')->groupBy('type_id'),
            'channels'      => Notification::getChannels()
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $this->notification->updateSettings($this->userId, $request->input('settings'));

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
     *
     * @deprecated
     */
    public function url(string $id)
    {
        /** @var \Coyote\Notification $notification */
        $notification = $this->notification->findOrFail($id, ['id', 'url', 'read_at', 'user_id', 'is_clicked']);

        $notification->is_clicked = true;

        if (!$notification->read_at) {
            $notification->read_at = Carbon\Carbon::now();

            broadcast(new NotificationRead($notification));
        }

        $notification->save();

        return redirect()->to($notification->url);
    }

    public function redirectToUrl()
    {
        $path = urldecode($this->request->get('path'));

        if (!$this->userId) {
            return redirect()->to($path);
        }

        /** @var \Coyote\Notification $notification */
        $notification = $this->auth->notifications()->where('url', $path)->first();

        if ($notification) {
            $notification->is_clicked = true;

            if (!$notification->read_at) {
                $notification->read_at = now();

                broadcast(new NotificationRead($notification));
            }

            $notification->save();
        }

        return redirect()->to($path);
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
            ->reject(fn (Notification $notification) => $notification->read_at !== null)
            ->pluck('id')
            ->all();

        if (!empty($ids)) {
            $this->notification->markAsRead($ids);
        }

        return count($ids);
    }
}
