<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Wallace\Vaults\Process;

class ProcessTest extends TestCase
{
    public function testThatItReturnsTheCommandOutput()
    {
        $process = new Process;

        $output = $process->exec('echo "foo\nbar"');

        $this->assertEquals(implode(PHP_EOL, ['foo', 'bar']), $output);
    }
}
