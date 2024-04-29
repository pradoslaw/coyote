<?php
namespace Neon\View\Components\Navigation;

use Neon\View\Html\Item;
use Neon\View\Html\Render;
use Neon\View\Html\Tag;
use Neon\View\Theme;

readonly class NavigationHtml implements Item
{
    public function __construct(
        private Navigation $navigation,
        private Theme      $theme)
    {
    }

    public function render(Render $h): array
    {
        return [
            $h->tag('header', ['class' => "container mx-auto flex {$this->theme->navigationColor} text-sm mb-4"], [
                $h->tag('div', ['class' => 'flex'], [
                    $h->tag('a',
                        ['id' => 'homepage', 'href' => $this->navigation->homepageUrl, 'class' => 'self-center mr-3.5'],
                        [$this->logo($h, ''),]),
                    $this->menuItems($h),
                ]),
                $h->tag('div',
                    [
                        'id'               => 'search-bar',
                        'class'            => "grow ml-10 {$this->theme->searchBarColor} whitespace-nowrap items-center hidden lg:flex",
                        'style'            => 'font-family:Arial;',
                        'content-editable' => 'true',
                    ],
                    [
                        $this->magnifyingGlass($h, "size-3.5 fill-current mr-2"),
                        $this->cssStylePlaceholder($h, '#search-bar input'),
                        $this->javaScript($h, 'function searchInputKeyPress(event) {
                            if (event.keyCode === 13) {
                                const url = "/Search?q=" + encodeURIComponent(event.target.value);
                                window.location.href = url;
                            }
                        }'),
                        $h->tag('input', [
                            'class'       => "outline-none bg-transparent w-full {$this->theme->searchBarActiveColor}",
                            'placeholder' => $this->navigation->searchBarTitle,
                            'onKeyPress'  => 'searchInputKeyPress(event)',
                        ], []),
                    ]),

                $h->tag('div', ['class' => 'flex'], [
                    $this->githubButton($h, 'mr-4'),
                    $this->controls($h),
                    $this->navigation->avatarVisible ?
                        $this->userAvatar($h)
                        : null,
                ]),
            ]),
        ];
    }

    private function magnifyingGlass(Render $h, string $className): Tag
    {
        return $h->html(<<<magnifyingGlass
            <?xml version="1.0" encoding="iso-8859-1"?>
            <svg class="$className" height="14px" width="14px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 490.4 490.4" xml:space="preserve">
              <g>
                <path d="M484.1,454.796l-110.5-110.6c29.8-36.3,47.6-82.8,47.6-133.4c0-116.3-94.3-210.6-210.6-210.6S0,94.496,0,210.796
                    s94.3,210.6,210.6,210.6c50.8,0,97.4-18,133.8-48l110.5,110.5c12.9,11.8,25,4.2,29.2,0C492.5,475.596,492.5,463.096,484.1,454.796z
                    M41.1,210.796c0-93.6,75.9-169.5,169.5-169.5s169.6,75.9,169.6,169.5s-75.9,169.5-169.5,169.5S41.1,304.396,41.1,210.796z"/>
              </g>
            </svg>
            magnifyingGlass,);
    }

    private function logo(Render $h, string $className): Tag
    {
        return $h->html(<<<logo
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="24" viewBox="0 0 17 24" fill="none" class="$className">
                <path d="M14.875 12.0501H13.4583C12.2847 12.0501 11.3333 11.0987 11.3333 9.92513V8.50846C11.3333 7.33486 12.2847 6.38346 13.4583 6.38346H14.875C16.0486 6.38346 17 5.43207 17 4.25846V2.8418C17 1.66819 16.0486 0.716797 14.875 0.716797H13.4583C12.2847 0.716797 11.3333 1.66819 11.3333 2.8418V4.25846C11.3333 5.43207 10.3819 6.38346 9.20833 6.38346H7.79167C6.61806 6.38346 5.66667 7.33486 5.66667 8.50846V9.92513C5.66667 11.0987 4.71527 12.0501 3.54167 12.0501H2.125C0.951395 12.0501 0 13.0015 0 14.1751V15.5918C0 16.7654 0.951395 17.7168 2.125 17.7168H9.20833C10.3819 17.7168 11.3333 18.6682 11.3333 19.8418V21.2585C11.3333 22.4321 12.2847 23.3835 13.4583 23.3835H14.875C16.0486 23.3835 17 22.4321 17 21.2585V14.1751C17 13.0015 16.0486 12.0501 14.875 12.0501Z" fill="#00A538"/>
            </svg>
            logo,);
    }

    private function menuItems(Render $h): Tag
    {
        return $h->tag('nav', [], [
            $h->tag('ul',
                ['class' => 'menu-items flex font-medium font-[Inter]'],
                \array_map(
                    fn(string $item, string $href) => $h->tag('li', [], [
                        $h->tag('a', [
                            'href'  => $href,
                            'class' => 'px-2 py-4 inline-block',
                        ], [$item]),
                    ]),
                    \array_keys($this->navigation->items),
                    $this->navigation->items,
                )),
        ]);
    }

    private function githubButton(Render $h, string $className): Tag
    {
        $icon = function (string $class) use ($h): Tag {
            return $h->html(<<<starIcon
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="$class">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
                starIcon,);
        };

        return $h->tag('div',
            ['class' => "github flex {$this->theme->githubButtonBorder} {$this->theme->githubButtonDivideX} rounded font-[Helvetica] font-bold text-xs self-center $className"],
            [
                $h->tag('a', [
                    'class' => 'name px-2.5 py-1.5 flex gap-x-2',
                    'href'  => $this->navigation->githubUrl,
                ], [
                    $icon('w-4 h-4'),
                    $this->navigation->githubName,
                ]),
                $h->tag('a', [
                    'class' => 'stars px-2.5 py-1.5 inline-block',
                    'href'  => $this->navigation->githubStarsUrl,
                ], [$this->navigation->githubStars]),
            ]);
    }

    private function controls(Render $h): Tag
    {
        return $h->tag('ul', ['class' => 'controls flex'], \array_map(fn(Link $link) => $this->controlItem($h, $link), $this->navigation->links));
    }

    private function controlItem(Render $h, Link $link): Tag
    {
        return $h->tag('li',
            ['class' => 'px-2 py-1.5 self-center ' . ($link->bold ? "rounded whitespace-nowrap {$this->theme->navigationControlItem}" : '')],
            [
                $h->tag('a', ['href' => $link->href], [$link->title]),
            ]);
    }

    private function cssStylePlaceholder(Render $h, string $cssSelector): Tag
    {
        $placeholderColor = $this->theme->searchBarColor;
        $line = [
            "$cssSelector:-moz-placeholder           {color:$placeholderColor; opacity: 1;}",
            "$cssSelector::-moz-placeholder          {color:$placeholderColor; opacity: 1;}",
            "$cssSelector::-ms-input-placeholder     {color:$placeholderColor;}",
            "$cssSelector::-webkit-input-placeholder {color:$placeholderColor;}",
            "$cssSelector::placeholder               {color:$placeholderColor; opacity: 1;}",
            "$cssSelector:placeholder-shown          {text-overflow:ellipsis;}",
        ];
        $styleSheet = \implode("\n", $line);
        return $h->html("<style>$styleSheet</style>");
    }

    private function userAvatar(Render $h): Tag
    {
        return $h->tag('div', ['class' => 'self-center'], [
            $h->tag('img', [
                'src'     => $this->navigation->avatarUrl,
                'class'   => "size-[30px] self-center rounded {$this->theme->navigationUserAvatarBorder}",
                'onClick' => 'toggleDropdown(event)',
                'id'      => 'userAvatar'], []),
            $h->tag('span',
                [
                    'parentClass' => 'relative',
                    'class'       => "px-6 py-1.5 inline-block cursor-pointer absolute z-[1] {$this->theme->navigationUserAvatarBorder} {$this->theme->navigationDropdownStyle} rounded top-9 right-0 hidden",
                    'onClick'     => 'logout()',
                    'id'          => 'logout',
                ],
                [$this->navigation->logoutTitle]),
            $this->javaScript($h, "
                const avatar = document.querySelector('#userAvatar');
                const logoutButton = document.querySelector('#logout');
                
                function toggleDropdown(event) {
                    logoutButton.classList.toggle('hidden');
                }
                
                function logout() {
                  fetch('/Logout', {method:'POST', headers:{'X-CSRF-TOKEN':'{$this->navigation->csrf}'}})
                    .then(r => window.location.reload());
                }
                "),
        ]);
    }

    private function javaScript(Render $h, string $sourceCode): Tag
    {
        return $h->html("<script>$sourceCode</script>");
    }
}
