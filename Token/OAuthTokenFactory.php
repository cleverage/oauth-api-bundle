<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 * Copyright (C) 2017-2023 Clever-Age
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Token;

use CleverAge\OAuthApiBundle\Exception\MissingResponseParameterException;

class OAuthTokenFactory implements OAuthTokenFactoryInterface
{
    public function createToken(array $data): OAuthTokenInterface
    {
        if (!array_key_exists('access_token', $data)) {
            throw MissingResponseParameterException::create($data, 'access_token');
        }

        return new OAuthToken(
            $data['access_token'],
            $data['token_type'] ?? 'Bearer',
            $data['expires_in'] ?? null,
            $data['scope'] ?? null,
            $data['jti'] ?? null
        );
    }
}
