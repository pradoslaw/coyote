<?php

namespace Coyote\Http\Controllers;

use Carbon\Carbon;
use Coyote\Flag\Type;
use Coyote\Notifications\FlagCreatedNotification;
use Coyote\Repositories\Contracts\FlagRepositoryInterface as FlagRepository;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\HasPermission;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Objects\Flag as Stream_Flag;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;

class FlagController extends Controller
{
    /**
     * @var FlagRepository
     */
    private $flag;

    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @param FlagRepository $flag
     * @param ForumRepository $forum
     * @param UserRepository $user
     */
    public function __construct(FlagRepository $flag, ForumRepository $forum, UserRepository $user)
    {
        parent::__construct();

        $this->flag = $flag;
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
        $rules = [
            'url'           => 'required|string',
            'metadata'      => 'required',
            'type_id'       => 'integer|exists:flag_types,id',
            'text'          => 'nullable|string',
            '_token'        => 'throttle'
        ];

        $validator = $this->getValidationFactory()->make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $metadata = $this->decrypt($request->get('metadata'));

            if (empty($metadata['permission'])) {
                $validator->errors()->add('metadata', trans('validation.string', ['attribute' => 'string']));
            }
        });

        $this->validateWith($validator);

        $flag = $this->transaction(function () use ($request) {
            $data = $request->all() + ['user_id' => $this->userId];
            $data['metadata'] = $this->decrypt($data['metadata']);

            $flag = $this->flag->create($data);

            stream(Stream_Create::class, (new Stream_Flag())->map($flag));

            return $flag;
        });

        $this->user->pushCriteria(new HasPermission($this->getPermissionName($flag)));

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
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(int $id)
    {
        $flag = $this->flag->findOrFail($id);
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
     * @return Encrypter
     */
    protected function getCryptFactory()
    {
        return app(Encrypter::class);
    }

    /**
     * @param $metadata
     * @return string
     */
    protected function decrypt($metadata)
    {
        return $this->getCryptFactory()->decrypt($metadata);
    }

    /**
     * @param \Coyote\Flag $flag
     * @return bool
     */
    private function isAuthorized($flag)
    {
        $gate = $this->getGateFactory();

        if (isset($flag->metadata->forum_id)) {
            $forum = $this->forum->findOrFail($flag->metadata->forum_id);

            return $gate->allows($flag->metadata->permission, $forum);
        } elseif (isset($flag->metadata->permission)) {
            return $gate->allows($flag->metadata->permission);
        } else {
            return false;
        }
    }

    /**
     * @param \Coyote\Flag $flag
     * @return string
     */
    private function getPermissionName($flag)
    {
        $permission = $flag->metadata->permission;

        if (isset($flag->metadata->forum_id)) {
            $permission = "forum-$permission";
        }

        return $permission;
    }
}
