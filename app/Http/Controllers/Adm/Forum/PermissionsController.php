<?php

namespace Coyote\Http\Controllers\Adm\Forum;

use Coyote\Events\ForumWasSaved;
use Coyote\Http\Controllers\Adm\BaseController;
use Coyote\Http\Grids\Adm\Forum\PermissionsGrid;
use Coyote\Permission;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\GroupRepositoryInterface as GroupRepository;
use Boduch\Grid\Source\CollectionSource;
use Coyote\Services\Forum\TreeBuilder\Builder;
use Coyote\Services\Forum\TreeBuilder\ListDecorator;
use Illuminate\Http\Request;

class PermissionsController extends BaseController
{
    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @var GroupRepository
     */
    private $group;

    /**
     * @param ForumRepository $forum
     * @param GroupRepository $group
     */
    public function __construct(ForumRepository $forum, GroupRepository $group)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->group = $group;
        $this->breadcrumb->push('Prawa dostępu', route('adm.forum.permissions'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $categoriesList = new ListDecorator(new Builder($this->forum->list()));

        return $this->view('adm.forum.permissions.home')->with('categoriesList', $categoriesList->setKey('id')->build());
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function edit(Request $request)
    {
        /** @var \Coyote\Forum $forum */
        $forum = $this->forum->findOrFail((int) $request->input('id'));
        $permissions = $forum->permissions()->get();
        $groups = $this->group->all();

        $this->breadcrumb->push($forum->name);

        $data = collect();

        // ugly way to get only forum permissions
        foreach (Permission::where('name', 'ilike', 'forum%')->get() as $permission) {
            $row = collect([
                'name' => $permission->name,
                'description' => $permission->description,
                'permission_id' => $permission->id
            ]);

            foreach ($groups as $group) {
                $filtered = $permissions->filter(function ($value) use ($group, $permission) {
                    return $value->group_id == $group->id && $value->permission_id == $permission->id;
                })->first();

                $row['group_' . $group->id] = isset($filtered->value) ? $filtered->value : $permission->default;
            }

            $data->push($row);
        }

        $grid = $this
            ->gridBuilder()
            ->createGrid(PermissionsGrid::class)
            ->setEnablePagination(false)
            ->setSource(new CollectionSource($data))
            ->setViewData(['http_method' => 'post', 'form_url' => route('adm.forum.permissions.save', ['id' => $forum->id])]);

        $builder = new Builder($this->forum->list());

        return $this->view('adm.forum.permissions.home', [
            'grid' => $grid,
            'categoriesList' => (new ListDecorator($builder))->build()
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        /** @var \Coyote\Forum $forum */
        $forum = $this->forum->findOrFail($request->input('id'));

        $this->transaction(function () use ($forum, $request) {
            $forum->permissions()->delete();

            foreach ($request->input('group') as $groupId => $permissions) {
                foreach ($permissions as $permissionId => $value) {
                    $forum->permissions()->create([
                        'group_id' => $groupId,
                        'permission_id' => $permissionId,
                        'value' => $value
                    ]);
                }
            }

            $this->flushPermission();
            event(new ForumWasSaved($forum));
        });

        return redirect()->route('adm.forum.permissions')->with('success', 'Zmiany uprawnień zostały zapisane.');
    }
}
