<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
//use Closure;
class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    /**
     * '/post' : 테스트용
     */
    protected $except = [

    ];

//    public function handle($request, Closure $next)
//    {
//        $response = $next($request);
//        $response->header('P3P', 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
//        return $response;
//    }
}
