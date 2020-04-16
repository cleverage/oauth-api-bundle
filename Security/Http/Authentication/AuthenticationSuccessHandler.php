<?php declare(strict_types=1);
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

/**
 * Redirect to URL in session if any else use default Symfony behaviour
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /** @var string */
    public const SESSION_KEY = 'sso_redirect_url';

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $session = $request->getSession();
        if ($session && $session->has(self::SESSION_KEY)) {
            $redirectUrl = $session->get(self::SESSION_KEY);
            if ($redirectUrl) {
                return $this->httpUtils->createRedirectResponse($request, $redirectUrl);
            }
        }

        return parent::onAuthenticationSuccess($request, $token);
    }
}
