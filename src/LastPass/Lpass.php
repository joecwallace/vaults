<?php

namespace Wallace\Vaults\LastPass;

use Wallace\Vaults\Process;
use Wallace\Vaults\Traits\RequiresKeys;

class Lpass
{
    use RequiresKeys;

    private $process = null;

    private $username;

    private $masterPassword;

    public function __construct(array $options)
    {
        $this->requireKeys(['username', 'master_password'], $options);

        $this->username = $options['username'];
        $this->masterPassword = $options['master_password'];
    }

    public function exec(string $command, string $piped = '')
    {
        return $this->runCommand($this->buildCommand($command, $piped));
    }

    private function runCommand($command)
    {
        return $this->getProcess()->exec($command);
    }

    private function buildCommand($args, $piped = '') : string
    {
        $authentication = $this->buildAuthenticateCommand();

        $command = "lpass {$args}";

        if (! empty($piped)) {
            $command = "({$piped} | {$command})";
        }

        return "{$authentication} && {$command} 2>&1";
    }

    private function buildAuthenticateCommand() : string
    {
        $password = escapeShellArg($this->masterPassword);

        return "(echo {$password} | LPASS_DISABLE_PINENTRY=1 lpass login --trust {$this->username})";
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
