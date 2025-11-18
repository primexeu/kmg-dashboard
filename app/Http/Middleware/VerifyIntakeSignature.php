<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyIntakeSignature
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('services.intake.secret');
        $sig = $request->header('X-Signature');
        if (!$sig) throw new AccessDeniedHttpException('Missing signature');

        $calc = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
        if (!hash_equals($calc, $sig)) {
            throw new AccessDeniedHttpException('Bad signature');
        }
        return $next($request);
    }
}
