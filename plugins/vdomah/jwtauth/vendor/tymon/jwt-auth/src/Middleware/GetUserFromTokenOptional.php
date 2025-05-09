<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tymon\JWTAuth\Middleware;

use Cms\Traits\ApiResponser;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class GetUserFromTokenOptional extends BaseMiddleware
{
    use ApiResponser;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if (! $token = $this->auth->setRequest($request)->getToken()) {
//            return $this->errorResponse('token_not_provided', self::$ERROR_CODES['AUTH_ERROR']);
        }

        try {
            $user = $this->auth->authenticate($token);
        } catch (TokenExpiredException $e) {
            $user = null;
        } catch (JWTException $e) {
            $user = null;
        }

        if ($user && $user->status_id > 0 && $user->id) {
            $this->events->fire('tymon.jwt.valid', $user);

            config()->set('auth.UserID', $user->id);

            config()->set('auth.UserType', $user->user_type);
            config()->set('auth.Token', $token);
        }


        $response = $next($request);

        traceLog([
            'URI' => $request->getRequestUri(),
            'METHOD' => $request->getMethod(),
            'REQUEST_BODY' => $request->all(),
            'RESPONSE' => $response->getContent()
        ]);

        return $response;
    }
}
