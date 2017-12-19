<?php

class SpamValidatorTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testValidateSpamLink()
    {
        $auth = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $auth->shouldReceive('check')->andReturn(false);

        $request = request();

        $validator = new \Coyote\Http\Validators\SpamValidator($auth, $request);

        // not spam link
        $this->assertTrue($validator->validateSpamLink([], 'java.io', [1]));
        $this->assertTrue($validator->validateSpamLink([], 'asp.net', [1]));
        // spam links
        $this->assertFalse($validator->validateSpamLink([], 'www.java.io', [1]));
        $this->assertFalse($validator->validateSpamLink([], 'http://www.java.io', [1]));
        $this->assertFalse($validator->validateSpamLink([], 'https://www.java.io', [1]));
        $this->assertFalse($validator->validateSpamLink([], 'https://java.io', [1]));
        $this->assertFalse($validator->validateSpamLink([], 'http://java.io', [1]));
        $this->assertFalse($validator->validateSpamLink([], 'http://java.io/foo.html?x=y', [1]));

        $this->assertTrue($validator->validateSpamLink([], 'foo@bar.net', [1]));
    }

    public function testValidateBlacklistHost()
    {
        $validator = $this->buildValidatorInstance('ip-37-109-33-137.a2mobile.pl');
        $this->assertFalse($validator->validateBlacklistHost()); // not allowed

        $validator = $this->buildValidatorInstance('ip-5-172-255-186.free.aero2.net.pl');
        $this->assertFalse($validator->validateBlacklistHost()); // not allowed

        $validator = $this->buildValidatorInstance('epp192.neoplus.adsl.tpnet.pl');
        $this->assertTrue($validator->validateBlacklistHost()); // allowed

        $validator = $this->buildValidatorInstance('ip-37-109-33-137.a2mobile.pl', true);
        $this->assertTrue($validator->validateBlacklistHost()); // allowed
    }

    private function buildValidatorInstance($userHost, $authorized = false)
    {
        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $request->shouldReceive('getHost')->andReturn($userHost);

        app()->instance(\Illuminate\Http\Request::class, $request);

        $auth = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $auth->shouldReceive('check')->andReturn($authorized);

        return new \Coyote\Http\Validators\SpamValidator($auth, $request);
    }
}
