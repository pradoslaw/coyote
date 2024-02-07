<?php
namespace Tests\Unit\Footer;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Footer;

class Test extends TestCase
{
    use Footer\Fixture\FooterStatements;

    /**
     * @test
     */
    public function copyrightYear()
    {
        $this->assertThat($this->footerStatements(), $this->containsIdentical('Copyright © 2000-2024'));
    }
}
