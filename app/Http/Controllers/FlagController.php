<?php
namespace Coyote\Http\Controllers;

use Carbon\Carbon;
use Coyote\Flag;
use Coyote\Flag\Type;
use Coyote\Notifications\FlagCreatedNotification;
use Coyote\Repositories\Criteria\HasPermission;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\Stream;
use Coyote\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FlagController extends Controller
{
    public function __construct(private UserRepository $user)
    {
        parent::__construct();
    }

    public function index(): Eloquent\Collection
    {
        return Type::query()->orderBy('order')->get();
    }

    public function save(Request $request): void
    {
        $this->validate($request, [
            'url'      => 'required|string',
            'metadata' => 'required',
            'type_id'  => 'integer|exists:flag_types,id',
            'text'     => 'nullable|string',
            '_token'   => 'throttle',
        ]);

        $metadata = decrypt($request->input('metadata'));
        $models = $permissions = [];

        foreach ($metadata as $resource => $id) {
            $model = \strToLower(class_basename($resource));
            $permissions[] = "$model-delete";
            $models[$model] = $id;
        }

        $flag = $this->transaction(function () use ($request, $models) {
            $data = $request->all() + ['user_id' => $this->userId];
            /** @var Flag $flag */
            $flag = Flag::query()->create($data);
            foreach ($models as $model => $id) {
                $relation = str_plural($model);
                $flag->$relation()->attach($id);
            }
            stream(Stream\Activities\Create::class, (new Stream\Objects\Flag())->map($flag));
            return $flag;
        });

        $this->user->pushCriteria(new HasPermission($permissions));
        $users = $this
            ->user
            ->all()
            ->sortByDesc(function (User $user): int {
                if ($user->id === $this->userId) {
                    return -2;
                }
                if ($user->is_online) {
                    return Carbon::now()->timestamp;
                }
                if ($user->visited_at) {
                    return $user->visited_at->timestamp;
                }
                return -1;
            })
            ->splice(0, 5);

        /** @var User $user */
        foreach ($users as $user) {
            $user->notify(new FlagCreatedNotification($flag));
        }
    }

    public function delete(Flag $flag): ?Response
    {
        $object = new Stream\Objects\Flag(['id' => $flag->id]);

        if (!$this->isAuthorized($flag)) {
            return response('Unauthorized.', 401);
        }

        $this->transaction(function () use ($flag, $object) {
            $flag->moderator_id = $this->userId;
            $flag->save();
            $flag->delete();
            stream(Stream\Activities\Delete::class, $object);
        });
        return null;
    }

    private function isAuthorized(Flag $flag): bool
    {
        /** @var Gate $gate */
        $gate = app(Gate::class);

        if (count($flag->forums)) {
            return $gate->allows('delete', $flag->forums[0]);
        }
        if (count($flag->microblogs)) {
            return $gate->allows('microblog-delete');
        }
        if (count($flag->jobs)) {
            return $gate->allows('job-delete');
        }
        return false;
    }
}
