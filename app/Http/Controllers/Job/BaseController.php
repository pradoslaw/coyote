<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Coyote\Services\Elasticsearch\Builders\Job\SearchBuilder;
use Lavary\Menu\Builder;
use Lavary\Menu\Item;
use Lavary\Menu\Menu;

abstract class BaseController extends Controller
{
    const TAB_ALL = 'all';
    const TAB_FILTERED = 'filtered';

    const DEFAULT_TAB = self::TAB_FILTERED;

    /**
     * @var Builder
     */
    private $tabs;

    /**
     * @var string
     */
    protected $tab = self::TAB_ALL;

    /**
     * @var JobRepository
     */
    protected $job;

    /**
     * @var SearchBuilder
     */
    protected $builder;

    /**
     * @param JobRepository $job
     */
    public function __construct(JobRepository $job)
    {
        parent::__construct();

        $this->public['promptUrl'] = route('job.tag.prompt');

        $this->job = $job;
        // we need to display actual number of active offers so don't remove line below!
        $this->job->pushCriteria(new PriorDeadline());

        $this->tabs = app(Menu::class)->make('_jobs', function (Builder $menu) {
            foreach (config('laravel-menu._jobs') as $title => $row) {
                $data = array_pull($row, 'data');
                $menu->add($title, $row)->data($data);
            }
        });
    }

    /**
     * @param string|null $view
     * @param array $data
     * @return \Illuminate\View\View
     */
    public function view($view = null, $data = [])
    {
        return parent::view($view, array_merge($data, [
            'tabs'          => $this->getTabs(),
            'tab'           => $this->tab,
            'count'         => $this->job->count()
        ]));
    }

    /**
     * @return \Lavary\Menu\Builder
     */
    protected function getTabs()
    {
        $this->tabs->filter(function (Item $item) {
            if ($item->data('role') === true) {
                return $this->userId !== null && $this->job->forUser($this->userId)->exists();
            }

            return true;
        });

        $icon = app('html')->tag('i', '', ['id' => 'btn-editor', 'class' => 'fa fa-cog', 'title' => 'Ustaw swoje preferencje']);

        $this->tabs->get('filtered')->append($icon);
        $this->tabs->get($this->tab)->active();

        return $this->tabs;
    }
}
