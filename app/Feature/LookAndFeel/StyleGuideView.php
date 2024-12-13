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
                const names = variableNames();
                window.addEventListener("load", () => {
                    const container = document.getElementById("atoms");
                    names.forEach(name => {                    
                        const code = document.createElement("code");
                        code.textContent = name;
                        
                        const line = document.createElement("div");
                        line.style.marginBottom = "4px";
                        line.appendChild(code);
                        
                        container.appendChild(line);
                    });
                });
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
            <div style="display:flex;">
                <div style="margin-right:24px; margin-left:24px;">
                    <h2 style="font-family:sans-serif;">4programmers - Primitive colors</h2>
                    @foreach ($colorGroups as $groupName => $colors)
                        <h3 style="font-size:1.1em; margin:0;">{{$groupName}}</h3>
                        <div style="display:flex; flex-wrap:wrap; margin-bottom:8px;">
                            @foreach ($colors as $colorName => $colorValue)
                              <div style="border: 1px solid #d0d0d0; text-align:center; width:64px;">
                                <div class="color-preview" style="background:{{$colorValue}}"></div>
                                <p style="margin-bottom:0;">
                                    <code style="font-size:0.6em;">{{$colorName}}</code>
                                </p>
                                <code>{{$colorValue}}</code>
                              </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div>
                    <h2 style="font-family:sans-serif;">4programmers - Atom elements</h2>
                    <div id="atoms"></div>
                </div>
            </div>',
            ['colorGroups' => $colorGroups]);
    }
}
