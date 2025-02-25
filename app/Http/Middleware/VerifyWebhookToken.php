<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyWebhookToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try
        {
            if(!$request->exists('token'))
            {
                throw new \Exception("no token provided");
            }

            $token = $request->get('token', null);

            if($token != env('SEARCH_REINDEX_TOKEN'))
            {
                throw new \Exception("given token (md5: " . md5($token) . ") is not valid");
            }

        }
        catch (\Exception $e)
        {
            Log::error($e->getMessage());
            return response([
                'status' => 1,
                'error' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}
