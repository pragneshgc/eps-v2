<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Check if the incoming ip is in the safeIP list
 */
class CheckIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = null;        

        $safeip = DB::table('SafeIP')->where('Status', 1)->where('SafeIP', $ipaddress)->select('SafeIP')->first();

        if(!isset($safeip->SafeIP) || $ipaddress != $safeip->SafeIP){
            return redirect('https://www.esasys.co.uk/');
        }

        return $next($request);
    }
}
