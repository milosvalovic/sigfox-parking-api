<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class ApiUser
{
    public function handle($request, Closure $next)
    {
        $user = User::select('*')->where('user_token', '=' ,$request->bearerToken())->first();
        if (isset($user)) {
            if($user->user_email_verified != 1){
                return response()->json([
                    'result' => false,
                    'error' => "Emailová adresa používateľa nie je overná"
                ], 401);
            } else {
                $request->merge(['user' => $user]);
                return $next($request);
            }
        }

        return response()->json([
            'result' => false,
            'error' => "Používateľ nie je prihlásený"
        ], 401);
    }
}
