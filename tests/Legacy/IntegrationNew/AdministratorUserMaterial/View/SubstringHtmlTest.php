<?php
namespace Tests\Legacy\IntegrationNew\AdministratorUserMaterial\View;

use Coyote\Domain\Administrator\View\Html\SubstringHtml;
use Coyote\Domain\StringHtml;
use PHPUnit\Framework\TestCase;

class SubstringHtmlTest extends TestCase
{
    /**
     * @test
     */
    public function trimInline(): void
    {
        $preview = new SubstringHtml(new StringHtml('<code>Lorem Ipsum</code> Bar'), 7);
        $this->assertSame('<code>Lorem I...</code>', "$preview");
    }
}
