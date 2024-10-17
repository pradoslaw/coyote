<?php
namespace Coyote\Http\Controllers\Adm;

use Coyote\Domain\Icon\FontAwesomePro;
use Illuminate\Database\Connection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class IconsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb->push('Ikony', route('adm.icons'));
    }

    public function index(FontAwesomePro $icons, Connection $connection): View
    {
        return $this->view('adm.icons', [
            'editorIcons' => $this->editorIcons(\array_filter($icons->icons()), $connection),
        ]);
    }

    private function editorIcons(array $icons, Connection $connection): array
    {
        $record = $connection->table('settings_key_value')->where('key', 'currentIcons')->first();
        if ($record) {
            $sessionIcons = \json_decode($record->value, true);
        } else {
            $sessionIcons = [];
        }
        $editorIcons = [];
        foreach ($icons as $iconName => $defaultIcon) {
            $editorIcons[$iconName] = [$defaultIcon, $sessionIcons[$iconName] ?? ''];
        }
        return $editorIcons;
    }

    public function save(Connection $connection): RedirectResponse
    {
        $currentIcons = $this->request->get('icons');
        Session::put('icons', $currentIcons);
        $connection->table('settings_key_value')->insert([
            'key'   => date('Y-m-d H:i:s'),
            'value' => json_encode($currentIcons),
        ]);
        $connection->table('settings_key_value')->updateOrInsert(
            ['key' => 'currentIcons'],
            ['value' => json_encode($currentIcons)]);
        return response()->redirectToRoute('adm.icons');
    }
}
