<?php

namespace App\Http\Middleware;

use Closure;
use App\Helper\JWTToken;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenVerificationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('token');
        $result = JWTToken::VerifyToken($token);
    
        if ($result == "unauthorized") {
            return response('Unauthorized', 401);
        } else {
            if (is_object($result) && property_exists($result, 'userEmail')) {
                $request->headers->set('email', $result->userEmail);
                $request->headers->set('id', $result->userID);
                return $next($request);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid token format (missing or invalid userEmail property)',
                    'result' => $result,
                ], 401);
            }
        }
    }
}
