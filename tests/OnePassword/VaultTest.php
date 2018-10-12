<?php

namespace Tests\OnePassword;

use PHPUnit\Framework\TestCase;
use Tests\Traits\ProvidesOptions;
use Wallace\Vaults\Exceptions\RequiredOptionException;
use Wallace\Vaults\OnePassword\Vault;
use Wallace\Vaults\OnePassword\Op;
use Wallace\Vaults\Process;

class VaultTest extends TestCase
{
    use ProvidesOptions;

    public function testThatTheInitNamedConstructorRequiresTheVaultIdOption()
    {
        $options = $this->getOptions();
        unset($options['vault_id']);

        $this->expectException(RequiredOptionException::class);

        Vault::init($options);
    }

    public function testThatItCanFindAnExistingItem()
    {
        $processStub = $this->createMock(Process::class);
        $processStub->method('exec')->with($this->stringContains('get item asdf'))->willReturn(json_encode([
            'details' => [
                'fields' => [
                    ['name' => 'username', 'value' => 'test.user'],
                    ['name' => 'password', 'value' => 's3cr3t'],
                ],
            ],
        ]));

        $foundItem = $this->createVaultWithProcess($processStub)->find('asdf');

        $this->assertEquals(['username' => 'test.user', 'password' => 's3cr3t'], $foundItem);
    }

    public function testThatItCanStoreANewItem()
    {
        $processStub = $this->createMock(Process::class);
        $processStub->method('exec')->with($this->eitherGetTemplateOrCreateItem())->will($this->returnEncodedJson([
            'get template Login' => [
                'fields' => [
                    ['name' => 'username', 'value' => 'foo'],
                    ['name' => 'password', 'value' => 'bar'],
                    ['name' => 'baz', 'value' => 'quux'],
                ],
            ],
            'create item Login' => ['uuid' => 'some-uuid'],
        ]));

        $newUuid = $this->createVaultWithProcess($processStub)->store('My item', 'test.user', 's3cr3t');

        $this->assertEquals('some-uuid', $newUuid);
    }

    public function testThatItCanDeleteAnItem()
    {
        $processStub = $this->createMock(Process::class);
        $processStub->expects($this->once())->method('exec')->with($this->stringContains('delete item jkl'))->willReturn(json_encode([
            'foo' => 'bar',
        ]));

        $deletedItem = $this->createVaultWithProcess($processStub)->delete('jkl');
    }

    private function createVaultWithProcess(Process $process) : Vault
    {
        $op = new Op($this->getOptions());
        $op->setProcess($process);

        return new Vault($op, 'fooBarVault');
    }

    private function eitherGetTemplateOrCreateItem()
    {
        return $this->logicalOr(
            $this->stringContains('get template Login'),
            $this->logicalAnd(
                $this->stringContains('create item Login'),
                $this->stringContains('My item'),
                $this->stringContains(base64_encode(trim(json_encode([
                    'fields' => [
                        ['name' => 'username', 'value' => 'test.user'],
                        ['name' => 'password', 'value' => 's3cr3t'],
                        ['name' => 'baz', 'value' => 'quux'],
                    ],
                ]), '='))),
                $this->stringContains('fooBarVault')
            )
        );
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
