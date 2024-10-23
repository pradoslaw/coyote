<?php
namespace V3\Test\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EmptyTest extends TestCase
{
    #[Test]
    public function test(): void
    {
        // test that calling js function, then php controller calls input boundary via spy (that needs selenium)
        // > same as before, but mock out the actual call (test that js makes request, and that controller when gets call delegates to IB)
        
        // test that twig view can get new function
    }
}
