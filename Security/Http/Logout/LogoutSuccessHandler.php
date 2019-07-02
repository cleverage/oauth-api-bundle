<?php declare(strict_types=1);
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Security\Http\Logout;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;

/**
 * Use the redirectUrl parameter to generate a redirect response after logout
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class LogoutSuccessHandler extends DefaultLogoutSuccessHandler
{
    /**
     * {@inheritDoc}
     */
    public function onLogoutSuccess(Request $request)
    {
        if ($request->query->has('redirectUrl')) {
            $redirectUrl = $request->query->get('redirectUrl');

            return $this->httpUtils->createRedirectResponse($request, $redirectUrl);
        }

        return parent::onLogoutSuccess($request);
    }
}
