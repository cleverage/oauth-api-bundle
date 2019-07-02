<?php declare(strict_types=1);
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Token;

use function array_key_exists;
use CleverAge\OAuthApiBundle\Exception\OAuthAuthenticationException;

/**
 * {@inheritDoc}
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class OAuthToken implements OAuthTokenInterface
{
    /** @var string */
    protected $accessToken;

    /** @var string */
    protected $tokenType;

    /** @var int|null */
    protected $expiresIn;

    /** @var string|null */
    protected $scope;

    /** @var string|null */
    protected $jti;

    /**
     * @param string      $accessToken
     * @param string      $tokenType
     * @param int|null    $expiresIn
     * @param string|null $scope
     * @param string|null $jti
     */
    public function __construct(string $accessToken, string $tokenType, ?int $expiresIn, ?string $scope, ?string $jti)
    {
        $this->accessToken = $accessToken;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->scope = $scope;
        $this->jti = $jti;
    }

    /**
     * @param array $response
     *
     * @return OAuthTokenInterface
     */
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

    /**
     * @return string
     */
    public function getAuthorization(): string
    {
        return "Bearer {$this->getAccessToken()}";
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    /**
     * @return int|null
     */
    public function getExpiresIn(): ?int
    {
        return $this->expiresIn;
    }

    /**
     * @return string|null
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @return string|null
     */
    public function getJti(): ?string
    {
        return $this->jti;
    }
}
