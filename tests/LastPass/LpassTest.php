<?php

namespace Tests\OnePassword;

use PHPUnit\Framework\TestCase;
use Tests\Traits\ProvidesOptions;
use Wallace\Vaults\Exceptions\RequiredOptionException;
use Wallace\Vaults\LastPass\Lpass;
use Wallace\Vaults\Process;

class LpassTest extends TestCase
{
    use ProvidesOptions;

    public function testThatItRequiresTheUsernameOption()
    {
        $this->unsetRequiredOptionToTriggerException('username');
    }

    public function testThatItRequiresTheMasterPasswordOption()
    {
        $this->unsetRequiredOptionToTriggerException('master_password');
    }

    public function testThatItAuthenticatesIfNotLoggedIn()
    {
        $processMock = $this->getMockBuilder(Process::class)->setMethods(['exec'])->getMock();
        $processMock->expects($this->once())->method('exec')->with($this->equalTo(
            $this->prependAuthenticationCommandTo('lpass arbitrary command 2>&1')
        ));

        $lpass = new Lpass($this->getOptions());
        $lpass->setProcess($processMock);

        $lpass->exec('arbitrary command');
    }

    public function testThatItPassesArgumentsThroughToLpass()
    {
        $lpassCommand = 'arbitrary arguments here';

        $processMock = $this->getMockBuilder(Process::class)->setMethods(['exec'])->getMock();
        $processMock->expects($this->once())->method('exec')->with($this->equalTo(
            $this->prependAuthenticationCommandTo('lpass arbitrary arguments here 2>&1')
        ));

        $lpass = new Lpass($this->getOptions());
        $lpass->setProcess($processMock);

        $lpass->exec($lpassCommand);
    }

    public function testThatItReturnsTheProcessOutput()
    {
        $processMock = $this->getMockBuilder(Process::class)->setMethods(['exec'])->getMock();
        $processMock->expects($this->once())->method('exec')->with($this->equalTo(
            $this->prependAuthenticationCommandTo('lpass lpass arguments here 2>&1')
        ))->willReturn('really good command output');

        $lpass = new Lpass($this->getOptions());
        $lpass->setProcess($processMock);

        $output = $lpass->exec('lpass arguments here');

        $this->assertEquals('really good command output', $output);
    }

    public function testThatItCanOptionallyPipeThingsToTheCommand()
    {
        $pipe = "echo 'something'";

        $processMock = $this->getMockBuilder(Process::class)->setMethods(['exec'])->getMock();
        $processMock->expects($this->once())->method('exec')->with($this->equalTo(
            $this->prependAuthenticationCommandTo('(' . $pipe . ' | lpass lpass arguments here) 2>&1')
        ))->willReturn('really good command output');

        $lpass = new Lpass($this->getOptions());
        $lpass->setProcess($processMock);

        $output = $lpass->exec('lpass arguments here', $pipe);

        $this->assertEquals('really good command output', $output);
    }

    private function prependAuthenticationCommandTo(string $command)
    {
        $options = $this->getOptions();

        return "(echo '{$options['master_password']}' | LPASS_DISABLE_PINENTRY=1 lpass login --trust {$options['username']}) && {$command}";
    }

    private function expectAuthenticationAttemptAnd($other)
    {
        $options = $this->getOptions();

        return $this->logicalOr(
            'lpass status 2>&1',
            "LPASS_DISABLE_PINENTRY=1 bash -c \"echo '{$options['master_password']}' | lpass login --trust {$options['username']}\"",
            $other
        );
    }

    private function unsetRequiredOptionToTriggerException($option)
    {
        $options = $this->getOptions();
        unset($options[$option]);

        $this->expectException(RequiredOptionException::class);

        new Lpass($options);
    }
}
