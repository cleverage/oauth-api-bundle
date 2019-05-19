<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Settings;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

/**
 * OAuth Settings container
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class OAuthSettings
{
    /** @var string */
    protected $baseUrl;

    /** @var array */
    protected $authenticationParams;

    /** @var string */
    protected $tokenRequestPath;

    /**
     * @param string $baseUrl
     * @param array  $authenticationParams
     * @param string $tokenRequestPath
     */
    public function __construct(string $baseUrl, array $authenticationParams, string $tokenRequestPath = '/oauth/token')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->authenticationParams = $authenticationParams;
        $this->tokenRequestPath = $tokenRequestPath;
    }

    /**
     * Always without trailing slash
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return RequestInterface
     */
    public function getTokenRequest(): RequestInterface
    {
        return new Request(
            'POST',
            $this->getBaseUrl().$this->tokenRequestPath,
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query($this->authenticationParams, '', '&')
        );
    }
}
