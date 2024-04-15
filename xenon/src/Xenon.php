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
        return \implode('', \array_map($this->ssrItem(...), $this->view));
    }

    private function ssrItem(ViewItem $tag): string
    {
        if ($tag instanceof TagField) {
            return "<$tag->htmlTag>" . \htmlSpecialChars($this->state[$tag->fieldName]) . "</$tag->htmlTag>";
        }
        if ($tag instanceof Tag) {
            return "<$tag->htmlTag>" .
                \implode('', \array_map($this->ssrItem(...), $tag->children)) .
                "</$tag->htmlTag>";
        }
        if ($tag instanceof Text) {
            return \htmlspecialchars($tag->text);
        }
    }

    private function spaVNodes(): string
    {
        return \implode(',', \array_map($this->spaItem(...), $this->view));
    }

    private function spaItem(ViewItem $tag): string
    {
        if ($tag instanceof TagField) {
            return "h('$tag->htmlTag', {}, [store['$tag->fieldName']])";
        }
        if ($tag instanceof Tag) {
            $children = \implode(',', \array_map($this->spaItem(...), $tag->children));
            return "h('$tag->htmlTag', {}, [$children])";
        }
        if ($tag instanceof Text) {
            return \json_encode($tag->text);
        }
    }
}
