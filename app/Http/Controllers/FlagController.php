<?php

namespace Coyote\Http\Controllers;

use Coyote\Flag\Type;
use Coyote\Repositories\Contracts\FlagRepositoryInterface as FlagRepository;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
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
     * @param FlagRepository $flag
     * @param ForumRepository $forum
     */
    public function __construct(FlagRepository $flag, ForumRepository $forum)
    {
        parent::__construct();

        $this->flag = $flag;
        $this->forum = $forum;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('flag', [
            'types'         => Type::all(),
            'url'           => $request->query('url'),
            'metadata'      => $request->query('metadata')
        ]);
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
            'text'          => 'string',
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

        $this->transaction(function () use ($request) {
            $data = $request->all() + ['user_id' => $this->userId];
            $data['metadata'] = $this->decrypt($data['metadata']);

            $flag = $this->flag->create($data);
            $object = (new Stream_Flag())->map($flag);

            stream(Stream_Create::class, $object);
        });
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response|null
     */
    public function delete($id)
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
     * @return mixed
     */
    public function modal()
    {
        return view('flag.modal');
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
}
