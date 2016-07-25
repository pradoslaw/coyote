<?php

namespace Coyote\Http\Controllers;

use Illuminate\Http\Request;
use Lavary\Menu\Menu;

class SearchController extends Controller
{
    /**
     * @var Request
     */
    private $request;

    /**
     * SearchController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        $this->request = $request;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Szukaj', route('search'));

        return $this->view('search', [
            'tabs' => $this->tabs()
        ]);
    }

    /**
     * @return mixed
     */
    private function tabs()
    {
        return app(Menu::class)->make('tabs', function ($menu) {
            $menu->add('Posty na forum', $this->route('posts'));
            $menu->add('Oferty pracy', $this->route('offers'));
            $menu->add('Mikroblogi', $this->route('microblogs'));
            $menu->add('Artykuły', $this->route('wiki'));
        });
    }

    /**
     * @param string$type
     * @return string
     */
    private function route($type)
    {
        return route('search', ['type' => $type, 'q' => $this->request->input('q')]);
    }
}
