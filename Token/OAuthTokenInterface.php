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

/**
 * Stores a negociated OAuth token
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
interface OAuthTokenInterface
{
    /**
     * @param array $response
     *
     * @return self
     */
    public static function createFromResponse(array $response): self;

    /**
     * @return string
     */
    public function getAuthorization(): string;
}
