<?php
namespace Coyote\Feature\LookAndFeel;

use Illuminate\Support\Facades\Blade;

class StyleGuideView
{
    public function view(array $colorGroups): string
    {
        return Blade::render('
            <link rel="stylesheet" href="{{ cdn(\'css/stylesEager.css\') }}">
            <script>
                variableNames();
                function variableNames() {
                  const variableNames = Array.from(document.styleSheets)
                    .filter(sheet => ownStyle(sheet))
                    .reduce((acc, sheet) => [...acc, ...sheetCssVariables(sheet)], []);
                  const unique = [...new Set(variableNames)];
                  unique.sort();
                  return unique;
                }
                function ownStyle(sheet) {
                  return sheet.href === null || sheet.href.startsWith(window.location.origin);
                }
                function sheetCssVariables(sheet) {
                  return Array.from(sheet.cssRules).reduce((acc, rule) => ruleCssVariables(rule, acc), []);
                }
                function ruleCssVariables(rule, acc) {
                  if (rule.style) {
                    if (rule.cssText.startsWith(\'@font-face\')) {
                    } else {
                      if (rule.selectorText.includes(\'look-and-feel\')) {
                        return [...acc, ...ruleStyleCssVariables(rule)];
                      }
                    }
                  }
                  return acc;
                }
                function ruleStyleCssVariables(rule) {
                  return Array.from(rule.style)
                    .filter(name => name.startsWith("--"))
                    .map(name => name.substr(2));
                }
            </script>
            <style>
              body { background: #e0e0e0; }
              .color-preview {
                  min-width: 60px;
                  height: 40px;
              }
              code {
                  background:#d0d0d0;
                  padding:1px 5px;
                  border-radius:4px;
              }
            </style>
            <h2 style="font-family:sans-serif;">4programmers - Primitive colors</h2>
            @foreach ($colorGroups as $colors)
                <div style="display:flex; flex-wrap:wrap;">
                    @foreach ($colors as $colorName => $colorValue)
                      <div style="padding: 24px 16px; border: 1px solid #d0d0d0; text-align:center;">
                        <div class="color-preview" style="background:{{$colorValue}}"></div>
                        <p><code>{{$colorName}}</code></p>
                        <code>{{$colorValue}}</code>
                      </div>
                    @endforeach
                </div>
            @endforeach
        ',
            ['colorGroups' => $colorGroups]);
    }
}
