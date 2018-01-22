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
        $data = Stat::selectRaw('round(avg(execution_time), 5) as execution_time, count(*) as requests, UNIX_TIMESTAMP(created_at) as date')
                    ->where('created_at', '>=', Carbon::now()->subDay())
                    ->groupBy(DB::raw('UNIX_TIMESTAMP(created_at) div 600'))
                    ->orderBy('created_at', 'asc')
                    ->get();
        return response()->json($data);
    }

    public function endpoints()
    {
        $data = Stat::selectRaw('count(*) as requests, routes.route')
                    ->where('stats.created_at', '>=', Carbon::now()->subDay())
                    ->join('routes', 'routes.id', '=', 'stats.route_id')
                    ->groupBy('route_id')
                    ->get();
        return response()->json($data);
    }
}