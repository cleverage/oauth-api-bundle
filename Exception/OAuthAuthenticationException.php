<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Exception;

use CleverAge\OAuthApiBundle\Settings\OAuthSettings;
use Psr\Http\Message\ResponseInterface;

/**
 * Thrown when unable to authenticate against the OAuth server API
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class OAuthAuthenticationException extends \RuntimeException
{
    /**
     * @param OAuthSettings     $authSettings
     * @param ResponseInterface $response
     *
     * @return OAuthAuthenticationException
     */
    public static function createFromTokenResponse(OAuthSettings $authSettings, ResponseInterface $response): self
    {
        $m = "Unable to get OAuth token from remote server '{$authSettings->getBaseUrl()}': ";
        $m .= "{$response->getStatusCode()} {$response->getReasonPhrase()}";

        return new self($m);
    }
}
