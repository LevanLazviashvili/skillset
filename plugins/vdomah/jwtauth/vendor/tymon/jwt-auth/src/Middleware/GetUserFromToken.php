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

class GetUserFromToken extends BaseMiddleware
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
            return $this->errorResponse('token_not_provided', self::$ERROR_CODES['AUTH_ERROR']);
        }

        try {
            $user = $this->auth->authenticate($token);
        } catch (TokenExpiredException $e) {
            return $this->errorResponse('token_expired', self::$ERROR_CODES['REFRESH_TOKEN_ERROR']);
        } catch (JWTException $e) {
            return $this->errorResponse( 'token_invalid',self::$ERROR_CODES['AUTH_ERROR']);
        }

        if (! $user) {
            return $this->errorResponse( 'invalid_auth_token', self::$ERROR_CODES['AUTH_ERROR']);
        }

        if ($user->status_id < 0) {
            return $this->errorResponse('user_deleted',self::$ERROR_CODES['USER_DELETED'] );
        }
        if (!$user->id) {
            return $this->errorResponse( 'invalid_auth_token', self::$ERROR_CODES['AUTH_ERROR']);
        }

        $response = $next($request);

        $this->events->fire('tymon.jwt.valid', $user);
        config()->set('auth.UserID', $user->id);
        config()->set('auth.UserType', $user->user_type);
        config()->set('auth.Token', $token);

        traceLog([
            'URI' => $request->getRequestUri(),
            'METHOD' => $request->getMethod(),
            'REQUEST_BODY' => $request->all(),
            'RESPONSE' => $response->getContent()
        ]);

        return $response;
    }
}
