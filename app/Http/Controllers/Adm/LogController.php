<?php

namespace Coyote\Http\Controllers\Adm;

use Illuminate\Http\Request;

class LogController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function index(Request $request)
    {
        $this->breadcrumb->push('Logi');

        $logs = $this
            ->getLogViewer()
            ->read($request->input('file', $request->input('file')))
            ->sort($request->input('sort', 'date'), $request->input('order', 'desc'))
            ->paginate()
            ->appends($request->except('page'));

        return $this->view('adm.log')->with(compact('logs'));
    }
}
