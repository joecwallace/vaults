<?php

namespace Wallace\Vaults;

class Process
{
    public function exec(string $command) : string
    {
        $output = [];

        exec($command, $output);

        return implode(PHP_EOL, $output);
    }
}
