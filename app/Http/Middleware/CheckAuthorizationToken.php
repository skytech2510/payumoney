<?php

namespace App\Http\Middleware;

use Closure;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;
use Exception;

class CheckAuthorizationToken
{
    public function __construct(TokenRepository $tokens, JwtParser $jwt)
    {
        $this->jwt    = $jwt;
        $this->tokens = $tokens;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION_TOKEN']))
        {
            $authToken = $_SERVER['HTTP_AUTHORIZATION_TOKEN'];
        }
        elseif (isset($_SERVER['HTTP_AUTHORIZATION']))
        {
            $authToken = $_SERVER['HTTP_AUTHORIZATION'];
        }
        else
        {
            $authToken = null;
        }

        if (empty($authToken)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        } else {
            $accessToken = $this->findUserAccessToken($authToken);
            try {
                if (!$accessToken) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
                return $next($request);
            } catch (Exception $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
        return $next($request);
    }

    protected function findUserAccessToken($generatedToken)
    {
        try {
            return $this->tokens->find(
                $this->jwt->parse($generatedToken)->claims()->get('jti')
            );
        } catch (Exception $e) {
            return false;
        }
    }
}