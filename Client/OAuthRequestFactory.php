<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package. * Copyright (C) 2017-2021 Clever-Age * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Client;

use CleverAge\OAuthApiBundle\Exception\OAuthAuthenticationException;
use CleverAge\OAuthApiBundle\Token\OAuthToken;
use CleverAge\OAuthApiBundle\Token\OAuthTokenInterface;
use Nyholm\Psr7\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Handles authentication token negotiation to simplify request creation
 */
class OAuthRequestFactory implements OAuthTokenAwareRequestFactoryInterface
{
    protected ?OAuthTokenInterface $token = null;

    public function __construct(
        protected ClientInterface $client,
        protected RequestFactoryInterface $requestFactory,
        protected string $baseUrl,
        protected string $tokenRequestPath,
        protected array $authenticationParams,
    ) {
    }

    public function createRequest(string $method, $uri): RequestInterface
    {
        return $this->requestFactory->createRequest($method, $this->baseUrl.$uri)
            ->withHeader('Authorization', $this->getToken()->getAuthorization());
    }

    public function getToken(): OAuthTokenInterface
    {
        if (!$this->token) {
            $this->token = $this->updateToken(
                $this->createTokenRequest(),
                static function (array $data) {
                    return OAuthToken::createFromResponse($data);
                }
            );
        }

        return $this->token;
    }

    protected function updateToken(RequestInterface $request, callable $method): OAuthTokenInterface
    {
        $response = $this->client->sendRequest($request);

        $content = (string) $response->getBody();
        if (!$content) {
            throw OAuthAuthenticationException::createFromTokenResponse($response, $this->tokenRequestPath);
        }

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw OAuthAuthenticationException::createFromTokenResponse($response, $this->tokenRequestPath);
        }

        try {
            return $method($data);
        } catch (\Exception) {
            throw OAuthAuthenticationException::createFromTokenResponse($response, $this->tokenRequestPath);
        }
    }

    protected function createTokenRequest(): RequestInterface
    {
        return $this->requestFactory->createRequest('POST', $this->baseUrl.$this->tokenRequestPath)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(Stream::create(http_build_query($this->authenticationParams)));
    }
}
