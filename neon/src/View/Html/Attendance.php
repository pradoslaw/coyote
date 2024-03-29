<?php
namespace Neon\View\Html;

readonly class Attendance implements Item
{
    public function __construct(
        private string $totalUsers,
        private string $onlineUsers,
        private string $totalUsersTitle,
        private string $onlineUsersTitle,
    )
    {
    }

    public function html(callable $h): array
    {
        $title = 'text-[#a2b7a0] text-xs';
        $number = 'text-[#f2f2f2] text-2xl leading-5 font-semibold mt-1';
        $separator = 'border-r border-white border-opacity-20';

        return [
            $h('div', [
                $h('div', [
                    $h('div', [$this->totalUsersTitle], ['class' => $title, 'id' => 'totalTitle']),
                    $h('div', [$this->totalUsers], ['class' => $number, 'id' => 'totalAmount']),
                ], "z-[3] px-6 w-1/2 $separator"),

                $h('div', [
                    $h('div', [
                        $this->diode($h),
                        $h('div', [$this->onlineUsersTitle], ['class' => "$title ml-1", 'id' => 'onlineTitle']),
                    ], 'flex items-center'),
                    $h('div', [$this->onlineUsers], ['class' => $number, 'id' => 'onlineAmount']),
                ], 'z-[3] px-6 w-1/2'),

                $this->bottomCenterHighlight($h),
            ], [
                'class' => 'flex align-center bg-black rounded-lg py-7',
                'id'    => 'attendance',
            ]),
        ];
    }

    private function diode(callable $h): string
    {
        return $h('div', [], 'size-2 bg-[#80ff00] rounded');
    }

    private function bottomCenterHighlight(callable $h): Tag
    {
        return $h('div', [], [
            'class'       => 'top-6 z-[2]',
            'style'       => \implode('', [
                'width:580px;',
                'height:580px;',
                'border-radius:580px;',
                'background:rgba(0, 165, 56, 0.3);',
                'filter:blur(50px);',
                'position:absolute;',
                'left:50%;',
                'transform:translateX(-50%)',
            ]),
            'parentClass' => 'relative overflow-hidden',
        ]);
    }
}
