<?php

namespace Tests\LastPass;

use PHPUnit\Framework\TestCase;
use Tests\Traits\ProvidesOptions;
use Wallace\Vaults\Exceptions\RequiredOptionException;
use Wallace\Vaults\LastPass\Vault;
use Wallace\Vaults\LastPass\Lpass;
use Wallace\Vaults\Process;

class VaultTest extends TestCase
{
    use ProvidesOptions;

    public function testThatTheInitNamedConstructorRequiresTheVaultIdOption()
    {
        $this->assertInstanceOf(Vault::class, Vault::init($this->getOptions()));
    }

    public function testThatItCanFindAnExistingItem()
    {
        $processStub = $this->createMock(Process::class);
        $processStub->method('exec')->with($this->stringContains('lpass show SOME_ID'))->willReturn(json_encode([
            [
                'username' => 'some.test.user',
                'password' => 'plainTextPassword',
            ],
        ]));

        $foundItem = $this->createVaultWithProcess($processStub)->find('SOME_ID');

        $this->assertEquals(['username' => 'some.test.user', 'password' => 'plainTextPassword'], $foundItem);
    }

    public function testThatItCanStoreANewItem()
    {
        $processStub = $this->createMock(Process::class);
        $processStub->expects($this->exactly(2))->method('exec')->with($this->logicalOr(
            $this->stringContains("echo \"Username: test.user; Password: 's3cr3t'\" | lpass add --non-interactive 'My item'"),
            $this->stringContains("lpass show 'My item'")
        ))->willReturn(json_encode(['id' => 'some-uuid']));

        $newUuid = $this->createVaultWithProcess($processStub)->store('My item', 'test.user', 's3cr3t');

        $this->assertEquals('some-uuid', $newUuid);
    }

    public function testThatItCanDeleteAnItem()
    {
        $processStub = $this->createMock(Process::class);
        $processStub->method('exec')->with($this->stringContains('rm jkl'))->willReturn(json_encode([
            'foo' => 'bar',
        ]));

        $deletedItem = $this->createVaultWithProcess($processStub)->delete('jkl');

        $this->assertEquals(['foo' => 'bar'], $deletedItem);
    }

    private function createVaultWithProcess(Process $process) : Vault
    {
        $lpass = new Lpass($this->getOptions());
        $lpass->setProcess($process);

        return new Vault($lpass);
    }

    private function returnEncodedJson(array $matchAndPayload)
    {
        return $this->returnCallback(function ($argument) use ($matchAndPayload) {
            foreach ($matchAndPayload as $match => $payload) {
                if (strpos($argument, $match) !== false) {
                    return json_encode($payload);
                }
            }
        });
    }
}
