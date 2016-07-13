<?php

namespace Coyote\Http\Controllers;

use Coyote\Flag\Type;
use Coyote\Repositories\Contracts\FlagRepositoryInterface as Flag;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Objects\Flag as Stream_Flag;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;

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
            $object = new Stream_Flag(['id' => $flag->id, 'displayName' => excerpt($request->text)]);

            stream(Stream_Create::class, $object);
        });
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function delete($id)
    {
        $flag = $this->flag->findOrFail($id);
        $object = new Stream_Flag(['id' => $flag->id]);

        // @todo Jezeli raportowany jest post na forum to sprawdzane jest globalne uprawnienie danego
        // uzytkownika. Oznacza to, ze lokalni moderatorzy nie beda mogli czytac raportow
        if (!empty($flag->metadata->permission) && $this->getGateFactory()->denies($flag->metadata->permission)) {
            return response('Unauthorized.', 401);
        }

        $flag->moderator_id = $this->userId;
        $flag->save();

        $flag->delete();
        stream(Stream_Delete::class, $object);
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
}
