<?php

namespace App\Http\Controllers;

use App\BlockMetric;
use App\Http\Controllers\Controller as BaseController;

class BlockMetricsController extends BaseController
{
    public function difficulty($blocks)
    {
        $data = BlockMetric::selectRaw('height, UNIX_TIMESTAMP(timestamp) as timestamp, difficulty div 1000000000000 as difficulty')
                           ->orderBy('height', 'asc')
                           ->take($blocks)
                           ->get();
        return response()->json($data);
    }

    public function hashrate($blocks)
    {
        $data = BlockMetric::selectRaw('height, UNIX_TIMESTAMP(timestamp) as timestamp, estimatedhashrate div 1000000000 as estimatedhashrate')
                           ->orderBy('height', 'asc')
                           ->take($blocks)
                           ->get();
        return response()->json($data);
    }
}