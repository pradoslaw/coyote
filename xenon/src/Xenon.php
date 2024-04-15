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
        $body = \implode('', \array_map($this->item(...), $this->view));
        return <<<html
            <head>
            <script>
                const xenon = {
                    setState: function(key, value) {
                        document.querySelector('i').innerHTML = value;
                    }
                };
            </script>
            </head>
            <body>$body</body>
            html;
    }

    private function item(ViewItem $tag): string
    {
        if ($tag instanceof TagField) {
            return "<$tag->htmlTag>" . $this->state[$tag->fieldName] . "</$tag->htmlTag>";
        }
        if ($tag instanceof Tag) {
            return "<$tag->htmlTag>" . \implode(\array_map($this->item(...), $tag->children)) . "</$tag->htmlTag>";
        }
    }
}
