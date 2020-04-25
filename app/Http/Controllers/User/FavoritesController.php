<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Repositories\Contracts\SubscribableInterface;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

class FavoritesController extends BaseController
{
    use HomeTrait;

    public function __construct()
    {
        parent::__construct();

        $this->breadcrumb->push('Ulubione i obserwowane strony', route('user.favorites'));
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return redirect()->action('User\FavoritesController@forum');
    }

    /**
     * @param TopicRepository $topic
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function forum(TopicRepository $topic)
    {
        $topic->pushCriteria(new OnlyThoseWithAccess(auth()->user()));
        $this->breadcrumb->push('Wątki na forum', route('user.favorites.forum'));

        return $this->load($topic);
    }

    /**
     * @param JobRepository $job
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function job(JobRepository $job)
    {
        $this->breadcrumb->push('Oferty pracy', route('user.favorites.job'));

        return $this->load($job);
    }

    /**
     * @param MicroblogRepository $microblog
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function microblog(MicroblogRepository $microblog)
    {
        $this->breadcrumb->push('Mikroblogi', route('user.favorites.microblog'));

        return $this->load($microblog);
    }

    /**
     * @param WikiRepository $wiki
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function wiki(WikiRepository $wiki)
    {
        $this->breadcrumb->push('Artykuły', route('user.favorites.wiki'));

        return $this->load($wiki);
    }

    /**
     * @param SubscribableInterface $repository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function load(SubscribableInterface $repository)
    {
        $subscribed = $repository->getSubscribed($this->userId);

        return $this->view(
            'user.favorites',
            [
                'tabs' => $this->getTabs(),
                'partial' => $this->request->route()->getName(),
                'subscribed' => $subscribed,
                'paginate' => $subscribed->links()
            ]
        );
    }

    /**
     * @return mixed
     */
    protected function getTabs()
    {
        return app(Menu::class)->make('favorites', function (Builder $menu) {
            $tabs = [
                'user.favorites.forum' => 'Wątki na forum',
                'user.favorites.job' => 'Oferty pracy',
                'user.favorites.microblog' => 'Mikroblogi',
                'user.favorites.wiki' => 'Artykuły'
            ];

            foreach ($tabs as $route => $label) {
                $item = $menu->add("<span>$label</span>", ['route' => $route]);
                $item->link->attr(['class' => 'nav-item']);

                if ($route === request()->route()->getName()) {
                    $item->link->active();
                }
            }
        });
    }
}
