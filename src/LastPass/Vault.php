<?php

namespace Wallace\Vaults\LastPass;

use Wallace\Vaults\Vault as BaseVault;

class Vault implements BaseVault
{
    private $lpass;

    public function __construct(Lpass $lpass)
    {
        $this->lpass = $lpass;
    }

    public static function init($options) : self
    {
        return new static(new Lpass($options));
    }

    public function find(string $id) : array
    {
        $results = array_map(function ($item) {
            return [
                'username' => $item['username'],
                'password' => $item['password'],
            ];
        }, $this->execAndDecode("show {$id}"));

        return reset($results);
    }

    public function store(string $service, string $username, string $password) : string
    {
        $service = escapeShellArg($service);
        $password = escapeShellArg($password);

        $this->lpass->exec("add --non-interactive {$service}", "echo \"Username: {$username}; Password: {$password}\"");

        return $this->execAndDecode("show {$service}")['id'];
    }

    public function delete(string $id) : array
    {
        return $this->execAndDecode("rm {$id}");
    }

    private function execAndDecode(string $command) : array
    {
        return json_decode($this->lpass->exec($command), true);
    }
}
