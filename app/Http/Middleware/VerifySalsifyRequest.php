<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class VerifySalsifyRequest
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

        if(!env('SALSIFY_NO_AUTH', false))
        {
            try
            {
                $headers = $request->headers;
                Log::debug("headers:");
                Log::debug($headers->__toString());
                Log::debug("end headers");

                $signature = $request->header('X-Salsify-Signature-V1');
                $timestamp = $request->header('X-Salsify-Timestamp');
                $certUrl = $request->header('X-Salsify-Cert-Url');
                $requestId = $request->header('X-Salsify-Request-ID');
                $orgId = $request->header('X-Salsify-Organization-ID');
                $endpointUrl = str_replace('http://', 'https://', $request->fullUrl());
                $requestBody = $request->getContent();

                Log::debug("signature:{$signature}");
                Log::debug("timestamp:{$timestamp}");
                Log::debug("certUrl:{$certUrl}");
                Log::debug("requestId:{$requestId}");
                Log::debug("orgId:{$orgId}");
                Log::debug("endpointUrl:{$endpointUrl}");
                Log::debug("requestBody:{$requestBody}");

                if(Carbon::now()->diffInMinutes(new Carbon(\Datetime::createFromFormat('U', $timestamp))) > 5)
                {
                    Log::warning("possible replay attack");
                    throw new \Exception("Salsify timestamp {$timestamp} is older than 5 minutes");
                }
                Log::debug("timestamp passed check");

                if(empty($certUrl))
                {
                    throw new \Exception("no X-Salsify-Cert-Url header value");
                }

                $parts = parse_url($certUrl);

                if($parts['scheme'] != 'https')
                {
                    throw new \Exception("cert url does not use https scheme");
                }

                if($parts['host'] != 'webhooks-auth.salsify.com')
                {
                    throw new \Exception("Salsify cert url does is not correct host: {$parts['host']}");
                }


                $certResponse = Http::get($certUrl);
                if(!$certResponse->successful())
                {
                    throw new \Exception("could not download certificate from {$certUrl}");
                }

                $publicKey = openssl_get_publickey($certResponse->body());
                if(!$publicKey)
                {
                    throw new \Exception("could not read public key");
                }

                $keyDetails = openssl_pkey_get_details($publicKey);

                Log::debug("public key:");
                Log::debug($keyDetails['key']);
                Log::debug("end public key");

                $checkString = implode('.', [$timestamp, $requestId, $orgId, $endpointUrl, trim($requestBody, "\n ")]);
                Log::debug("checkString:");
                Log::debug($checkString);
                Log::debug("end checkString");

                $verified = openssl_verify($checkString, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256);

                if(!$verified)
                {
                    throw new \Exception("could not verify signature of request");
                }

                Log::debug("Salsify request signature verified");

            }
            catch (\Exception $e)
            {
                Log::error($e->getMessage());
                return response([
                    'status' => 1,
                    'error' => $e->getMessage()
                ], 401);
            }
        }

        return $next($request);
    }
}
