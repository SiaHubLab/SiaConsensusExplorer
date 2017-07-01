<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Hash;

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
        $request['height'] = $height;
        $this->validate($request, [
            'height' => 'required|numeric',
        ]);

        $cache_key = "block_".$height;
        if (!Cache::has($cache_key)) {
            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->request('GET', env('SIA_ADDRESS').'/consensus/blocks/'.$height);
                $block = json_decode($res->getBody(), true);
                Cache::put($cache_key, $block, 60);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Ooops, our wallet unavailable. Please try later.'], 503);
            }
        }

        return response()->json(Cache::get($cache_key));
    }

    /**
     * Get multiple raw consensus blocks
     *
     * Load raw json blocks from siad by height
     *
     * @return return json
     */
    public function getBlocks(Request $request)
    {
        $this->validate($request, [
            'blocks' => 'required',
        ]);

        $blocks = [];
        foreach (array_unique($request->input('blocks')) as $height) {
            $cache_key = "block_".$height;
            if (!Cache::has($cache_key)) {
                try {
                    $client = new \GuzzleHttp\Client();
                    $res = $client->request('GET', env('SIA_ADDRESS').'/consensus/blocks/'.$height);
                    $block = json_decode($res->getBody(), true);
                    Cache::put($cache_key, $block, 60);
                    $blocks[] = $block;
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Ooops, our wallet unavailable. Please try later.'], 503);
                }
            } else {
                $blocks[] = Cache::get($cache_key);
            }
        }

        return response()->json($blocks);
    }

    /**
     * Get hash info from index
     *
     * Get hash type and block references for any type hash
     *
     * @param string $hash Consensus any hash(id)
     * @return return json
     */
    public function getHash($hash, Request $request)
    {
        $request['hash'] = $hash;
        $this->validate($request, [
            'hash' => 'required|alpha_num|between:64,100',
        ]);

        $cache_key = "hash_".$hash;
        if (!Cache::has($cache_key)) {
            $hash = Hash::with('blocks')
                             ->where('hash', $hash)
                             ->first();

            Cache::put($cache_key, $hash, 60);
        }

        return response()->json(Cache::get($cache_key));
    }

    /**
     * Return last N known hashes
     *
     * Undocumented function long description
     *
     * @return return json
     */
    public function getLatest()
    {
        $latest = Hash::with('blocks')
                      ->join('block_hash_index', 'block_hash_index.hash_id', '=', 'hashes.id')
                      ->orderBy('block_hash_index.height', 'desc')
                      ->take(50)
                      ->get();


        return response()->json($latest);
    }

    /**
     * Return last N known hashes
     *
     * Undocumented function long description
     *
     * @return return json
     */
    public function getSearchResults($search, Request $request)
    {
        $request['hash'] = $search;
        $this->validate($request, [
            'hash' => 'required|alpha_num|between:64,100',
        ]);

        $searchResults = Hash::with('blocks')
                      ->where('hash', $search)
                      ->take(50)
                      ->get();


        return response()->json($searchResults);
    }
}
