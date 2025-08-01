<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');
        if (!$token) {
            return response()->json(['message' => 'API token required'], 401);
        }

        $user = User::where('api_token', $token)->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid API token'], 401);
        }

        Auth::setUser($user);

        return $next($request);
    }
}
