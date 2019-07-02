<?php declare(strict_types=1);
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Exception;

/**
 * Thrown when unable to request a remote API
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class ApiRequestException extends \RuntimeException
{
    /**
     * @param string     $uri
     * @param \Exception $e
     *
     * @return self
     */
    public static function create(string $uri, \Exception $e = null): self
    {
        return new self("Unable to reach remote API '{$uri}'", 0, $e);
    }
}
