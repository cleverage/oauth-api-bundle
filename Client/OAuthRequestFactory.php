<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 * Copyright (C) 2017-2023 Clever-Age
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Client;

use CleverAge\OAuthApiBundle\Exception\OAuthAuthenticationException;
use CleverAge\OAuthApiBundle\Token\OAuthTokenFactoryInterface;
use CleverAge\OAuthApiBundle\Token\OAuthTokenInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Handles authentication token negotiation to simplify request creation.
 */
class OAuthRequestFactory implements OAuthTokenAwareRequestFactoryInterface
{
    protected ?OAuthTokenInterface $token = null;

    public function __construct(
        protected ClientInterface $client,
        protected RequestFactoryInterface $requestFactory,
        protected StreamFactoryInterface $streamFactory,
        protected OAuthTokenFactoryInterface $tokenFactory,
        protected string $baseUrl,
        protected string $tokenRequestPath,
        protected array $authenticationParams,
        protected string $tokenRequestContentType = 'application/json',
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
            $this->token = $this->updateToken($this->createTokenRequest());
        }

        return $this->token;
    }

    protected function updateToken(RequestInterface $request): OAuthTokenInterface
    {
        $response = $this->client->sendRequest($request);
        if (200 !== $response->getStatusCode()) {
            throw OAuthAuthenticationException::createFromTokenResponse($response, $this->tokenRequestPath);
        }

        $content = (string) $response->getBody();
        if (!$content) {
            throw OAuthAuthenticationException::createFromTokenResponse($response, $this->tokenRequestPath);
        }

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw OAuthAuthenticationException::createFromTokenResponse($response, $this->tokenRequestPath);
        }

        try {
            return $this->tokenFactory->createToken($data);
        } catch (\Exception) {
            throw OAuthAuthenticationException::createFromTokenResponse($response, $this->tokenRequestPath);
        }
    }

    protected function createTokenRequest(): RequestInterface
    {
        $tokenRequest = $this->requestFactory->createRequest('POST', $this->baseUrl.$this->tokenRequestPath)
            ->withHeader('Content-Type', $this->tokenRequestContentType);

        if ('application/x-www-form-urlencoded' === $this->tokenRequestContentType) {
            $content = http_build_query($this->authenticationParams);
        } elseif ('application/json' === $this->tokenRequestContentType) {
            $content = json_encode($this->authenticationParams, JSON_THROW_ON_ERROR);
        } else {
            $m = "Unsupported token request content type {$this->tokenRequestContentType}";
            throw new \UnexpectedValueException($m);
        }

        return $tokenRequest->withBody($this->streamFactory->createStream($content));
    }
}
