<?php

namespace Wallace\Vaults\OnePassword;

use Wallace\Vaults\Process;
use Wallace\Vaults\Traits\RequiresKeys;

class Op
{
    use RequiresKeys;

    private $process = null;

    private $subdomain;

    private $username;

    private $secretKey;

    private $masterPassword;

    public function __construct(array $options)
    {
        $this->requireKeys(['subdomain', 'username', 'secret_key', 'master_password'], $options);

        $this->subdomain = $options['subdomain'];
        $this->username = $options['username'];
        $this->secretKey = $options['secret_key'];
        $this->masterPassword = $options['master_password'];
    }

    public function exec(string $command)
    {
        return $this->runCommand($this->buildCommand($command));
    }

    private function runCommand($command)
    {
        return $this->getProcess()->exec($command);
    }

    private function buildCommand($args) : string
    {
        return "{$this->buildAuthenticateCommand()} | op {$args} 2>&1";
    }

    private function buildAuthenticateCommand()
    {
        $args = [
            'op',
            'signin',
            $this->subdomain,
            $this->username,
            $this->secretKey,
            escapeShellArg($this->masterPassword),
            '--output=raw',
        ];

        return implode(' ', $args);
    }

    public function setProcess(Process $process) : void
    {
        $this->process = $process;
    }

    public function getProcess() : Process
    {
        if (is_null($this->process)) {
            $this->process = new Process([]);
        }

        return $this->process;
    }
}
