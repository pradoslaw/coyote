<?php

namespace Coyote\Http\Controllers;

use Lavary\Menu\Menu;

class SearchController extends Controller
{
    public function index()
    {
        $this->breadcrumb->push('Szukaj', route('search'));

        return $this->view('search', [
            'tabs' => $this->tabs()
        ]);
    }

    private function tabs()
    {
        return app(Menu::class)->make('tabs', function ($menu) {
            $menu->add('Posty na forum', route('search', ['type' => 'posts']));
            $menu->add('Oferty pracy', route('search', ['type' => 'offers']));
            $menu->add('Mikroblogi', route('search', ['type' => 'microblogs']));
            $menu->add('Artykuły', route('search', ['type' => 'wiki']));
        });
    }
}
