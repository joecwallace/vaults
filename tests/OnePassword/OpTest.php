<?php

namespace Tests\OnePassword;

use PHPUnit\Framework\TestCase;
use Tests\Traits\ProvidesOptions;
use Wallace\Vaults\Exceptions\RequiredOptionException;
use Wallace\Vaults\OnePassword\Op;
use Wallace\Vaults\Process;

class OpTest extends TestCase
{
    use ProvidesOptions;

    public function testThatItRequiresTheSubdomainOption()
    {
        $this->unsetRequiredOptionToTriggerException('subdomain');
    }

    public function testThatItRequiresTheUsernameOption()
    {
        $this->unsetRequiredOptionToTriggerException('username');
    }

    public function testThatItRequiresTheSecretKeyOption()
    {
        $this->unsetRequiredOptionToTriggerException('secret_key');
    }

    public function testThatItRequiresTheMasterPasswordOption()
    {
        $this->unsetRequiredOptionToTriggerException('master_password');
    }

    public function testThatItPassesArgumentsThroughToOp()
    {
        $opCommand = 'arbitrary arguments here';

        $processMock = $this->getMockBuilder(Process::class)->setMethods(['exec',])->getMock();
        $processMock->expects($this->once())->method('exec')->with($this->equalTo(
            "op signin SUBDOMAIN USERNAME SECRET_KEY 'MASTER_PASSWORD' --output=raw | op {$opCommand} 2>&1"
        ));

        $op = new Op($this->getOptions());
        $op->setProcess($processMock);

        $op->exec($opCommand);
    }

    public function testThatItReturnsTheProcessOutput()
    {
        $processStub = $this->createMock(Process::class);
        $processStub->method('exec')->willReturn('really good command output');

        $op = new Op($this->getOptions());
        $op->setProcess($processStub);

        $output = $op->exec('op arguments here');

        $this->assertEquals('really good command output', $output);
    }

    private function unsetRequiredOptionToTriggerException($option)
    {
        $options = $this->getOptions();
        unset($options[$option]);

        $this->expectException(RequiredOptionException::class);

        new Op($options);
    }
}
