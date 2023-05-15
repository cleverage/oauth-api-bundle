<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 * Copyright (C) 2017-2023 Clever-Age
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Token;

/**
 * @see OAuthTokenInterface
 */
class OAuthToken implements OAuthTokenInterface
{
    public function __construct(
        protected string $accessToken,
        protected string $tokenType = 'Bearer',
        protected ?int $expiresIn = null,
        protected ?string $scope = null,
        protected ?string $jti = null,
    ) {
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
