<?php

namespace App\Http\Middleware;

use App\Route;
use App\Stat;
use Closure;
use Illuminate\Http\Request;

class Stats
{
    public function handle(Request $request, Closure $next)
    {
        $request->merge(['start_time' => microtime(true)]);
        return $next($request);
    }

    public function terminate(Request $request, $response)
    {
        if(strpos($request->route()->uri, 'health') === false) {
            $route = Route::firstOrCreate(['route' => $request->route()->uri]);
            Stat::create([
                'route_id' => $route->id,
                'execution_time' => microtime(true) - $request->get('start_time'),
                'source' => $request->ip(),
                'request_data' => json_encode($request->except(['start_time']))
            ]);
        }
    }
}
