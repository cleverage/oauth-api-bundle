<?php declare(strict_types=1);
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Action;

use CleverAge\OAuthApiBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Sets a URL to redirect to after an OAuth authentication
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class RedirectUrlSetterAction
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $redirectUrl = $request->query->get('redirectUrl');
        if (!$redirectUrl) {
            throw new NotFoundHttpException('Missing redirectUrl in query');
        }
        $session = $request->getSession();
        if ($session) {
            $session->set(AuthenticationSuccessHandler::SESSION_KEY, $redirectUrl);
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse();
        }

        return new RedirectResponse($redirectUrl);
    }
}
