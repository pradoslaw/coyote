<?php
namespace Xenon;

interface ViewItem
{
    public function ssrHtml(array $state): string;

    public function spaNode(): string;
}
