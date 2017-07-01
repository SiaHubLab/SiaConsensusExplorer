<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Cache;

class ExplorerController extends BaseController
{
    /**
     * Get raw consensus block
     *
     * Load raw json block from siad by height
     *
     * @param int $height Consensus block height
     * @return return json
     */
    public function getBlock($height, Request $request)
    {
        $this->validate([
            'height' => $height
        ], [
            'height' => 'required|numeric',
        ]);

        $cache_key = "block_".$height;
        if (!Cache::has($cache_key)) {
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', env('SIA_ADDRESS').'/consensus/blocks/'.$height);
            $block = json_decode($res->getBody(), true);
            Cache::put($cache_key, $block, 60);
        }

        return response()->json(Cache::get($cache_key));
    }

    /**
     * Get hash info from index
     *
     * Get hash type and block references for any type hash
     *
     * @param string $hash Consensus any hash(id)
     * @return return json
     */
    public function getHash($hash)
    {
        $this->validate([
            'hash' => $hash
        ], [
            'hash' => 'required|alpha_num|between:64,100',
        ]);

        $cache_key = "hash_".$hash;
        if (!Cache::has($cache_key)) {
            $hash = \App\Hash::with('blocks')
                             ->where('hash', $hash)
                             ->first();

            Cache::put($cache_key, $block, 60);
        }

        return response()->json(Cache::get($cache_key));
    }
}
