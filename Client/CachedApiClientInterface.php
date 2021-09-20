<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package. * Copyright (C) 2017-2021 Clever-Age * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Client;

/**
 * Additional method to manage cache for API requests
 */
interface CachedApiClientInterface extends ApiClientInterface
{
    public function invalidate(array $tags, bool $private = false): void;
}
