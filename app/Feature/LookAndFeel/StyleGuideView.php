<?php
namespace Coyote\Feature\LookAndFeel;

use Illuminate\Support\Facades\Blade;

class StyleGuideView
{
    public function view(array $colors): string
    {
        return Blade::render('
            <style>
              body { background: #e0e0e0; }
              .color-preview {
                  min-width: 90px;
                  height: 80px;
              }
              code {
                  background:#d0d0d0;
                  padding:1px 5px;
                  border-radius:4px;
              }
            </style>
            <h2 style="font-family:sans-serif;">4programmers - Style guide</h2>
            <div style="display:flex; flex-wrap:wrap;">
                @foreach ($colors as $colorName => $colorValue)
                  <div style="padding: 32px 24px; border: 1px solid #d0d0d0; text-align:center;">
                    <div class="color-preview" style="background:{{$colorValue}}"></div>
                    <p><code>{{$colorName}}</code></p>
                    <code>{{$colorValue}}</code>
                  </div>
                @endforeach
            </div>
        ',
            ['colors' => $colors]);
    }
}
