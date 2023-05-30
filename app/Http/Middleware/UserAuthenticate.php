<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class UserAuthenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        return response(['message' => 'Unauthenticated.', 'status' => 'auth-401']);
        return response()->json(['error' => 'Unauthenticated.'], 401);
        if (! $request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
//            return route('auth.login');
        }
    }
}
