<?php
namespace Coyote\Http\Controllers\Adm;

use Coyote\Domain\StringHtml;
use Illuminate\Foundation\Application;
use Illuminate\View\View;

class DashboardController extends BaseController
{
    public function index(): View
    {
        return $this->view('adm.dashboard', [
            'checklist' => [
                $this->directoryWritable('storage/', \storage_path()),
                $this->directoryWritable('uploads/', \public_path()),
                [
                    'label' => 'Redis włączony',
                    'value' => \config('cache.default'),
                ],
                [
                    'label' => new StringHtml('PHP - <code>' . \PHP_VERSION . '</code>'),
                    'value' => true,
                ],
                [
                    'label' => new StringHtml('Laravel - <code>' . Application::VERSION . '</code>'),
                    'value' => true,
                ],
            ],
        ]);
    }

    public function directoryWritable(string $basePath, string $path): array
    {
        $permission = \decOct(\filePerms($path) & 0777);
        return [
            'label' => new StringHtml("Katalog <code>$basePath</code> ma prawa do zapisu - <code>$permission</code>"),
            'value' => \is_writeable(\storage_path()),
        ];
    }
}
