<?php declare(strict_types=1);
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Request;

/**
 * Adding cache settings to standard API request
 */
interface CachedApiRequestInterface extends ApiRequestInterface
{
    /**
     * @return int
     */
    public function getTtl(): int;

    /**
     * @return bool
     */
    public function isPrivate(): bool;

    /**
     * @return array
     */
    public function getTags(): array;
}
