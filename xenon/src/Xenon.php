<?php
namespace Xenon;

readonly class Xenon
{
    private Fragment $view;

    public function __construct(array $view, private array $state)
    {
        $this->view = new Fragment($view);
    }

    public function html(): string
    {
        return <<<html
            <body>
              {$this->ssrView()}
              <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
              <script>{$this->spaView()}</script>
            </body>
            html;
    }

    private function ssrView(): string
    {
        return $this->view->ssrHtml($this->state);
    }

    private function spaView(): string
    {
        $spaState = \json_encode($this->state);
        return "
            const h = Vue.h;
            const app = Vue.createApp(() => {$this->view->spaExpression()});
            const store = Vue.reactive($spaState);
            const xenon = {
                setState(key, value) {
                    store[key] = value;
                }
            };
            window.addEventListener('load', () => app.mount('body'));
        ";
    }
}
