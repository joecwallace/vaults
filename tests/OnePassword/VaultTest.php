<?php

namespace Tests\OnePassword;

use PHPUnit\Framework\TestCase;
use Tests\Traits\ProvidesOptions;
use Wallace\Vaults\Exceptions\RequiredOptionException;
use Wallace\Vaults\OnePassword\Vault;

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
        // $vault->find()
    }

    public function testThatItCanStoreANewItem()
    {
        // $vault->store()
    }

    public function testThatItCanDeleteAnItem()
    {
        // $vault->delete()
    }
}
