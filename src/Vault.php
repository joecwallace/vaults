<?php

namespace Wallace\Vaults;

interface Vault
{
    public function find(string $id) : array;

    public function store(string $service, string $username, string $password) : string;

    public function delete(string $id) : array;
}
