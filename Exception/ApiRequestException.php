<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 * Copyright (C) 2017-2023 Clever-Age
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Exception;

/**
 * Thrown when unable to request a remote API.
 */
class ApiRequestException extends \RuntimeException
{
    public static function create(string $uri, \Exception $e = null): self
    {
        return new self("Unable to reach remote API '{$uri}'", 0, $e);
    }
}
