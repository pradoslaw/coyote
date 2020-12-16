<?php

namespace Coyote\Http\Controllers;

use Carbon\Carbon;
use Coyote\Flag;
use Coyote\Flag\Type;
use Coyote\Notifications\FlagCreatedNotification;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\HasPermission;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Objects\Flag as Stream_Flag;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;

class FlagController extends Controller
{
    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @param ForumRepository $forum
     * @param UserRepository $user
     */
    public function __construct(ForumRepository $forum, UserRepository $user)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->user = $user;
    }

    /**
     * @return Type[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return Type::all();
    }

    /**
     * @param Request $request
     */
    public function save(Request $request)
    {
        $this->validate($request, [
            'url'           => 'required|string',
            'metadata'      => 'required',
            'type_id'       => 'integer|exists:flag_types,id',
            'text'          => 'nullable|string',
            '_token'        => 'throttle'
        ]);

        $metadata = decrypt($request->input('metadata'));
        $models = $permissions = [];

        foreach ($metadata as $resource => $id) {
            $model = strtolower(class_basename($resource));
            $permissions[] = "$model-delete";

            $models[$model] = $id;
        }

        $flag = $this->transaction(function () use ($request, $models) {
            $data = $request->all() + ['user_id' => $this->userId];

            /** @var Flag $flag */
            $flag = Flag::create($data);

            foreach ($models as $model => $id) {
                $relation = str_plural($model);

                $flag->$relation()->attach($id);
            }

            stream(Stream_Create::class, (new Stream_Flag())->map($flag));

            return $flag;
        });

        $this->user->pushCriteria(new HasPermission($permissions));

        $users = $this
            ->user
            ->all()
            ->sortByDesc(function ($user) {
                /** @var \Coyote\User $user */
                return $user->id == $this->userId ? -1
                    : ($user->is_online ? Carbon::now()->timestamp : $user->visited_at->timestamp);
            })
            ->splice(0, 5);

        /** @var \Coyote\User $user */
        foreach ($users as $user) {
            $user->notify(new FlagCreatedNotification($flag));
        }
    }

    /**
     * @param Flag $flag
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Flag $flag)
    {
        $object = new Stream_Flag(['id' => $flag->id]);

        if (!$this->isAuthorized($flag)) {
            return response('Unauthorized.', 401);
        }

        $this->transaction(function () use ($flag, $object) {
            $flag->moderator_id = $this->userId;
            $flag->save();

            $flag->delete();

            stream(Stream_Delete::class, $object);
        });
    }

    /**
     * @param \Coyote\Flag $flag
     * @return bool
     */
    private function isAuthorized(Flag $flag)
    {
        $gate = $this->getGateFactory();

        if (count($flag->forums)) {
            return $gate->allows('delete', $flag->forums[0]);
        } elseif (count($flag->microblogs)) {
            return $gate->allows('microblog-delete');
        } elseif (count($flag->jobs)) {
            return $gate->allows('job-delete');
        } else {
            return false;
        }
    }
}
