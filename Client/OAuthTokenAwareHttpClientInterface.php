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

use CleverAge\OAuthApiBundle\Token\OAuthTokenInterface;
use Http\Client\HttpClient;

/**
 * Defines an HTTP client that can provide an OAuth Token
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
interface OAuthTokenAwareHttpClientInterface extends HttpClient
{
    /**
     * @return OAuthTokenInterface
     */
    public function getToken(): OAuthTokenInterface;
}
