<?php

namespace Coyote\Http\Controllers;

use Coyote\Flag;
use Coyote\Flag\Type;
use Illuminate\Http\Request;
use Coyote\Stream\Objects\Flag as Stream_Flag;
use Coyote\Stream\Activities\Create as Stream_Create;

class FlagController extends Controller
{
    public function index(Request $request)
    {
        return view('flag', [
            'types'         => Type::all(),
            'url'           => $request->query('url'),
            'metadata'      => $request->query('metadata')
        ]);
    }

    public function save(Request $request)
    {
        $this->validate($request, [
            'url'           => 'required|string',
            'metadata'      => 'json',
            'type_id'       => 'integer|exists:flag_types,id',
            'text'          => 'string'
        ]);

        \DB::transaction(function () use ($request) {
            $flag = Flag::create($request->all() + ['user_id' => $this->userId]);
            $object = new Stream_Flag(['id' => $flag->id, 'displayName' => excerpt($request->text)]);

            stream(Stream_Create::class, $object);
        });
    }
}
