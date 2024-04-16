<?php
namespace Xenon;

readonly class Xenon
{
    public function __construct(
        private array $view,
        private array $state,
    )
    {
    }

    public function html(): string
    {
        return <<<html
            <head>
              <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
              <script>{$this->spaView()}</script>
            </head>
            <body id="app">{$this->ssrView()}</body>
            html;
    }

    private function spaView(): string
    {
        $spaState = \json_encode($this->state);
        return "
            const h = Vue.h;
            const app = Vue.createApp(() => {$this->spaVNodes()});
            const store = Vue.reactive($spaState);
            const xenon = {
                setState(key, value) {
                    store[key] = value;
                }
            };
            window.addEventListener('load', () => app.mount('#app'));
        ";
    }

    private function ssrView(): string
    {
        return \implode('', \array_map(fn(ViewItem $item) => $item->ssrHtml($this->state), $this->view));
    }

    private function spaVNodes(): string
    {
        return '[' . \implode(',', \array_map(fn(ViewItem $item) => $item->spaNode(), $this->view)) . ']';
    }
}
