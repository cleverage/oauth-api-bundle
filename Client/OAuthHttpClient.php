<?php declare(strict_types=1);
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Client;

use CleverAge\OAuthApiBundle\Exception\OAuthAuthenticationException;
use CleverAge\OAuthApiBundle\Settings\OAuthSettings;
use CleverAge\OAuthApiBundle\Token\OAuthToken;
use CleverAge\OAuthApiBundle\Token\OAuthTokenInterface;
use Http\Client\Exception;
use Http\Client\HttpClient as HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Handles authentication token negotiation to simplify queries
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class OAuthHttpClient implements OAuthTokenAwareHttpClientInterface
{
    /** @var HttpClientInterface */
    protected $client;

    /** @var OAuthSettings */
    protected $settings;

    /** @var OAuthTokenInterface */
    protected $token;

    /**
     * @param HttpClientInterface $client
     * @param OAuthSettings       $settings
     */
    public function __construct(HttpClientInterface $client, OAuthSettings $settings)
    {
        $this->client = $client;
        $this->settings = $settings;
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $request = $request->withAddedHeader('Authorization', $this->getToken()->getAuthorization());

        return $this->client->sendRequest($request);
    }

    /**
     * @throws Exception
     *
     * @return OAuthTokenInterface
     */
    public function getToken(): OAuthTokenInterface
    {
        if (!$this->token) {
            $request = $this->settings->getTokenRequest();
            $response = $this->client->sendRequest($request);

            $content = (string) $response->getBody();
            if (!$content) {
                throw OAuthAuthenticationException::createFromTokenResponse($this->settings, $response);
            }

            $response = json_decode($content, true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw OAuthAuthenticationException::createFromTokenResponse($this->settings, $response);
            }
            $this->token = OAuthToken::createFromResponse($response);
        }

        return $this->token;
    }
}
