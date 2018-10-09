<?php

namespace Wallace\Vaults\OnePassword;

class Op
{
    private $subdomain;

    private $username;

    private $secretKey;

    private $masterPassword;

    private $sessionToken = '';

    public function __construct($subdomain = null, $username = null, $secretKey = null, $masterPassword = null)
    {
        // TODO: dependency on config()
        $this->subdomain = $subdomain ?? config('one-password.subdomain');
        $this->username = $username ?? config('one-password.username');
        $this->secretKey = $secretKey ?? config('one-password.secret-key');
        $this->masterPassword = $masterPassword ?? config('one-password.master-password');
    }

    public function exec(string $command)
    {
        return $this->runCommand($this->buildCommand($command));
    }

    private function runCommand($command)
    {
        $output = [];
        $exitCode = 0;

        exec($command, $output, $exitCode);

        return implode(PHP_EOL, $output);
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
}
