<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Router;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use CleverAge\OAuthApiBundle\Action\RedirectUrlSetterAction;

/**
 * Handles SSO urls
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class SsoRouter
{
    /** @var RouterInterface */
    protected $router;

    /** @var string */
    protected $loginUrl;

    /** @var string */
    protected $targetPathParameter;

    /**
     * @param RouterInterface $router
     * @param string          $loginUrl
     * @param string          $targetPathParameter
     */
    public function __construct(RouterInterface $router, string $loginUrl, string $targetPathParameter)
    {
        $this->router = $router;
        $this->loginUrl = $loginUrl;
        $this->targetPathParameter = $targetPathParameter;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function getLoginUrl(string $url): string
    {
        return strtr(
            $this->loginUrl,
            [
                '{{callback_url}}' => $url,
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function getRedirectUrl(Request $request): string
    {
        return $this->router->generate(
            RedirectUrlSetterAction::class,
            [
                'redirectUrl' => $request->getUri(),
            ]
        );
    }

    /**
     * @return string
     */
    public function getCallbackUrl(): string
    {
        return $this->router->generate('oauth_callback', [], RouterInterface::ABSOLUTE_URL);
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function getLogoutUrl(Request $request): string
    {
        return $this->router->generate(
            'oauth_logout',
            [
                $this->targetPathParameter => $request->getUri(),
            ]
        );
    }
}
