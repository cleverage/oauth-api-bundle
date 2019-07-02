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

/**
 * Allow to manage cache for API request
 */
interface CachedApiClientInterface extends ApiClientInterface
{
    /**
     * @param array $tags
     * @param bool  $private
     */
    public function invalidate(array $tags, bool $private = false): void;
}
