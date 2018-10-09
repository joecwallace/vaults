<?php

namespace Wallace\Vaults\Traits;

use Wallace\Vaults\Exceptions\RequiredOptionException;

trait RequiresKeys
{
    public function requireKeys($keys, array $options)
    {
        if (is_string($keys)) {
            $keys = [$keys];
        }

        foreach ($keys as $key) {
            if (empty($options[$key])) {
                throw new RequiredOptionException($key);
            }
        }
    }
}
