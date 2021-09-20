<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package. * Copyright (C) 2017-2021 Clever-Age * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Client;

use CleverAge\OAuthApiBundle\Request\ApiRequestInterface;

/**
 * Base logic used to query a remote API object
 */
interface ApiClientInterface
{
    public function query(ApiRequestInterface $apiRequest): object;
}
