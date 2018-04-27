<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Stat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use DB;

class HealthController extends BaseController
{
    public function main()
    {
        $cache_key = "health_main";
        if (!Cache::has($cache_key)) {
            $data = Stat::selectRaw('round(avg(execution_time), 5) as execution_time, count(*) as requests, UNIX_TIMESTAMP(created_at) as date')
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->groupBy(DB::raw('UNIX_TIMESTAMP(created_at) div 1800'))
                ->orderBy('created_at', 'asc')
                ->get();
            Cache::put($cache_key, $data, 15);
        }
        return response()->json(Cache::get($cache_key));
    }

    public function endpoints()
    {
        $cache_key = "health_endpoints";
        if (!Cache::has($cache_key)) {
            $data = Stat::selectRaw('count(*) as requests, routes.route')
                ->where('stats.created_at', '>=', Carbon::now()->subDay())
                ->join('routes', 'routes.id', '=', 'stats.route_id')
                ->groupBy('route_id')
                ->get();
            Cache::put($cache_key, $data, 15);
        }
        return response()->json(Cache::get($cache_key));
    }
}