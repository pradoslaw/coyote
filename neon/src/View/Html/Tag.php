<?php
namespace Neon\View\Html;

interface Tag
{
    public function html(): string;

    public function parentClass(): ?string;
}
