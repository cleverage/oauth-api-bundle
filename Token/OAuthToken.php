<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package. * Copyright (C) 2017-2021 Clever-Age * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Token;

use CleverAge\OAuthApiBundle\Exception\OAuthAuthenticationException;

/**
 * @see OAuthTokenInterface
 */
class OAuthToken implements OAuthTokenInterface
{
    public function __construct(
        protected string $accessToken,
        protected string $tokenType,
        protected ?int $expiresIn = null,
        protected ?string $scope = null,
        protected ?string $jti = null,
    ) {
    }

    public static function createFromResponse(array $response): OAuthTokenInterface
    {
        if (!array_key_exists('access_token', $response)) {
            throw new OAuthAuthenticationException('Missing "access_token" key in server response');
        }
        if (!array_key_exists('token_type', $response)) {
            throw new OAuthAuthenticationException('Missing "access_token" key in server response');
        }

        return new self(
            $response['access_token'],
            $response['token_type'],
            $response['expires_in'] ?? null,
            $response['scope'] ?? null,
            $response['jti'] ?? null
        );
    }

    public function getAuthorization(): string
    {
        return "Bearer {$this->getAccessToken()}";
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getExpiresIn(): ?int
    {
        return $this->expiresIn;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function getJti(): ?string
    {
        return $this->jti;
    }
}
