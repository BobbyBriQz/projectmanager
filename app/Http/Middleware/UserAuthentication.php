<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        try {
            $token = JWTAuth::parseToken()->getToken();
            $user = JWTAuth::toUser($token);
            if (is_null($user['id'])) {
                return response()->json(['error' => 'user_not_found'], 404);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['error' => 'token_expired'], 400);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['error' => 'token_invalid'], 400);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['error' => 'token_absent'], 400);

        }

        return $next($request);
    }
}
