<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ValidateRegistrationParams
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'max:12', 'min:7'],
            'name' => ['required', 'max:10', 'min:2'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'Error' => $validator->messages()->all(),
                'Message' => 'Invalid registration credentials'
            ], 401);
        }

        return $next($request);
    }
}
