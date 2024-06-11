<?php
namespace Coyote\Http\Controllers\Adm;

use Collective\Html\HtmlBuilder;
use Coyote\Http\Controllers\Controller;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

class BaseController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->breadcrumb->push('Panel administracyjny', route('adm.home'));
    }

    /**
     * @return Builder
     */
    protected function buildMenu()
    {
        return $this->getMenuFactory()->make('adm', function ($menu) {
            /** @var HtmlBuilder $html */
            $html = app('html');
            $fa = function ($icon) use ($html) {
                return $html->tag('i', '', ['class' => "fa $icon"]);
            };

            /** @var Builder $menu */
            $menu->add('Strona główna', ['route' => 'adm.dashboard'])->prepend($fa('fa-desktop fa-fw'));

            $menu->divide(['class' => 'menu-group-moderator-actions']);

            $menu->add('Użytkownicy', ['route' => 'adm.users'])->prepend($fa('fa-user fa-fw'));
            $menu->add('Dodane posty', ['url' => route('adm.flag')])->prepend($fa('fa-search fa-fw'));
            $menu->add('Dodane komentarze', ['url' => route('adm.flag', ['filter' => 'type:comment'])])->prepend($fa('fa-search fa-fw'));
            $menu->add('Dodane mikroblogi', ['url' => route('adm.flag', ['filter' => 'type:microblog'])])->prepend($fa('fa-search fa-fw'));
            $menu->add('Raporty', ['url' => route('adm.flag', ['filter' => 'is:reported not:deleted'])])->prepend($fa('far fa-flag fa-fw'));
            $menu->add('Bany', ['route' => 'adm.firewall'])->prepend($fa('fa-user-lock fa-fw'));
            $menu->add('Kto jest online', ['route' => 'adm.sessions'])->prepend($fa('fa-eye fa-fw'));
            $menu->add('Dziennik zdarzeń', ['route' => 'adm.stream'])->prepend($fa('fa-newspaper fa-fw'));
            $menu->add('Cenzura', ['route' => 'adm.words'])->prepend($fa('fa-highlighter fa-fw'));
            $menu->add('Tagi', ['route' => 'adm.tags'])->prepend($fa('fa-tag fa-fw'));

            $menu->divide(['class' => 'menu-group-service-operations']);

            $menu->add('Grupy', ['route' => 'adm.groups'])->prepend($fa('fa-users fa-fw'))->data('permission', 'adm-group');
            $menu->add('Kategorie', ['route' => 'adm.forum.categories'])->prepend($fa('fa-th-list fa-fw'));
            $menu->add('Uprawnienia w kategorii', ['route' => 'adm.forum.permissions'])->prepend($fa('fa-file-signature fa-fw'))->data('permission', 'adm-group');
            $menu->add('Powody moderacji', ['route' => 'adm.forum.reasons'])->prepend($fa('fa-eraser fa-fw'));
            $menu->add('Bloki statyczne', ['route' => 'adm.blocks'])->prepend($fa('far fa-file-code fa-fw'));
            $menu->add('Faktury i płatności', ['route' => 'adm.payments'])->prepend($fa('fa-shopping-cart fa-fw'))->data('permission', 'adm-payment');
        })
            ->filter(function ($item) {
                if ($item->data('permission')) {
                    return auth()->user()->can($item->data('permission'));
                }

                return true;
            });
    }

    /**
     * @inheritdoc
     */
    protected function view($view = null, $data = [])
    {
        return parent::view($view, array_merge($data, ['menu' => $this->buildMenu()]));
    }

    /**
     * @return Menu
     */
    protected function getMenuFactory()
    {
        return app(Menu::class);
    }

    /**
     * Clear users cache permission after updating groups etc.
     */
    protected function flushPermission()
    {
        $this->getCacheFactory()->tags('permissions')->flush();
    }
}
