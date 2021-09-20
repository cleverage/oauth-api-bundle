<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package. * Copyright (C) 2017-2021 Clever-Age * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Exception;

use Psr\Http\Message\ResponseInterface;

/**
 * Thrown when unable to authenticate against the OAuth server API
 */
class OAuthAuthenticationException extends \RuntimeException
{
    public static function createFromTokenResponse(ResponseInterface $response, string $tokenRequestPath): self
    {
        $m = "Unable to get OAuth token from remote server '{$tokenRequestPath}': ";
        $m .= "{$response->getStatusCode()} {$response->getReasonPhrase()}\n{$response->getBody()}";

        return new self($m);
    }
}
