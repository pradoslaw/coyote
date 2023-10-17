<?php
namespace Coyote\Http\Controllers;

class RenderParams
{
    public function __construct(public string|null $tagName)
    {
    }
}
