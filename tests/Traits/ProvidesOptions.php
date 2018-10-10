<?php

namespace Tests\Traits;

trait ProvidesOptions
{
    public function getOptions() : array
    {
        return [
            'subdomain' => 'SUBDOMAIN',
            'username' => 'USERNAME',
            'secret_key' => 'SECRET_KEY',
            'master_password' => 'MASTER_PASSWORD',
            'vault_id' => 'VAULT_ID',
        ];
    }
}
