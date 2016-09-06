<?php

namespace Coyote\Http\Controllers;

use Coyote\Services\Elasticsearch\Factories\GeneralFactory;
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

        return $this->view('search', $this->result())->with('tabs', $this->tabs());
    }

    private function result()
    {
        if (!$this->request->exists('q')) {
            return [];
        }

        $builder = (new GeneralFactory())->build($this->request);
        $body = $builder->build();

        $params = [
            'index'     => config('elasticsearch.default_index'),
            'type'      => 'topics,microblogs,wiki,jobs',
            'body'      => $body
        ];

        debugbar()->debug(json_encode($body));
        $response = $this->getClient()->search($params);

        dd($response, array_get($response, 'hits.total'));
    }

    /**
     * Get client instance
     *
     * @return mixed
     */
    protected function getClient()
    {
        return app('elasticsearch');
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
     * @param string $type
     * @return string
     */
    private function route($type)
    {
        return route('search', ['type' => $type, 'q' => $this->request->input('q')]);
    }
}
