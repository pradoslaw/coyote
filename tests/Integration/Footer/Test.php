<?php
namespace Tests\Integration\Footer;

use PHPUnit\Framework\TestCase;
use Tests\Integration\Footer;

class Test extends TestCase
{
    use Footer\Fixture\FooterStatements;
    use Footer\Fixture\Clock;

    /**
     * @test
     */
    public function copyrightYear()
    {
        $this->systemYear(2005);
        $this->assertThat($this->footerStatements(), $this->containsIdentical('Copyright © 2000-2005'));
    }

    /**
     * @test
     */
    public function executionTime()
    {
        $this->fixedExecutionTime(0.1245);
        $this->assertThat($this->footerStatements(), $this->containsIdentical('Coyote 2.5: 124 ms'));
    }

    /**
     * @test
     */
    public function executionTimeSeconds()
    {
        $this->fixedExecutionTime(2.4322);
        $this->assertThat($this->footerStatements(), $this->containsIdentical('Coyote 2.5: 2.43 s'));
    }
}
