<?php

namespace Wallace\Vaults\Exceptions;

class RequiredOptionException extends \InvalidArgumentException
{
    public function __construct(string $requiredFieldName)
    {
        parent::__construct("Required option not passed: \"{$requiredFieldName}\"");
    }
}
