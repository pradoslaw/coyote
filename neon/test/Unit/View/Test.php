<?php
namespace Neon\Test\Unit\View;

use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    /**
     * @test
     */
    public function docType(): void
    {
        $dom = new ViewDom($this->view(''));
        $this->assertSame('<!DOCTYPE html>', $dom->docType());
    }

    /**
     * @test
     */
    public function title(): void
    {
        $dom = new ViewDom($this->view('Winter is coming'));
        $this->assertSame(
            'Winter is coming',
            $dom->find('/html/head/title'));
    }

    private function view(string $title): string
    {
        return (new View($title))->html();
    }
}
