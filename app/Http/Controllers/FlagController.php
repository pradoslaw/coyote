<?php

namespace Coyote\Http\Controllers;

use Coyote\Flag\Type;
use Coyote\Repositories\Contracts\FlagRepositoryInterface as Flag;
use Illuminate\Http\Request;
use Coyote\Stream\Objects\Flag as Stream_Flag;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Activities\Delete as Stream_Delete;

class FlagController extends Controller
{
    /**
     * @var Flag
     */
    private $flag;

    /**
     * FlagController constructor.
     * @param Flag $flag
     */
    public function __construct(Flag $flag)
    {
        parent::__construct();
        $this->flag = $flag;
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
        $this->validate($request, [
            'url'           => 'required|string',
            'metadata'      => 'json',
            'type_id'       => 'integer|exists:flag_types,id',
            'text'          => 'string'
        ]);

        \DB::transaction(function () use ($request) {
            $flag = $this->flag->create($request->all() + ['user_id' => $this->userId]);
            $object = new Stream_Flag(['id' => $flag->id, 'displayName' => excerpt($request->text)]);

            stream(Stream_Create::class, $object);
        });
    }

    /**
     * @param $id
     */
    public function delete($id)
    {
        $flag = $this->flag->findOrFail($id);
        $object = new Stream_Flag(['id' => $flag->id]);

        $flag->moderator_id = $this->userId;
        $flag->save();

        $flag->delete();
        stream(Stream_Delete::class, $object);
    }
}
