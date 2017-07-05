<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Hash;

/**
 * @SWG\Info(title="SiaHub Explorer API", version="0.1")
 */
class ExplorerController extends BaseController
{
    /**
     * @SWG\Get(
     *     path="/api/block/{height}",
     *     summary="Load single consensus block from siad by height",
     *     @SWG\Parameter(
     *         name="height",
     *         in="path",
     *         description="Block height",
     *         required=true,
     *         type="integer"
     *     ),
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return raw consensus block"),
     *     @SWG\Response(response="422", description="Height must be numeric value"),
     *     @SWG\Response(response="503", description="Wallet unavailable, try later")
     * )
     */
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
     * @SWG\Post(
     *     path="/api/blocks",
     *     summary="Load multiple consensus blocks",
     *     @SWG\Parameter(
     *         name="blocks[]",
     *         in="formData",
     *         description="Blocks height array",
     *         required=true,
     *         type="array",
     *         collectionFormat="multi",
     *         @SWG\Items(
     *             type="integer"
     *         )
     *     ),
     *     consumes={"application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return raw consensus blocks array"),
     *     @SWG\Response(response="422", description="Block fields required"),
     *     @SWG\Response(response="503", description="Wallet unavailable, try later")
     * )
     */
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
        $response = [];


        foreach (array_unique($request->input('blocks')) as $height) {
            $cache_key = "block_".$height;
            if (!Cache::has($cache_key)) {
                try {
                    $client = new \GuzzleHttp\Client();
                    $res = $client->request('GET', env('SIA_ADDRESS').'/consensus/blocks/'.$height);
                    $block = json_decode($res->getBody(), true);
                    Cache::put($cache_key, $block, 60);
                } catch (\Exception $e) {
                    //return response()->json(['error' => 'Ooops, our wallet unavailable. Please try later.'], 503);
                    continue;
                }
            } else {
                $block = Cache::get($cache_key);
            }

            $response[$height]['height'] = $height;
            $response[$height]['headers'] = $block['blockheader'];
            //$response[$height]['raw'] = $block;
            switch ($request->input('type')) {
                case 'unlockhash':
                case 'siacoinoutputid':
                case 'siafundoutputid':
                case 'filecontractid':
                case 'filecontractrevisions':
                foreach ($block['minerpayouts'] as $scoid => $sco) {
                    $hash_check = ($request->input('type') == "unlockhash") ? $sco['unlockhash']:$scoid;
                    if ($hash_check == $request->input('hash')) {
                        $sco['id'] = $scoid;
                        $response[$height]['minerpayouts'][] = $sco;
                    }
                }

                foreach ($block['transactions'] as $trid => $tr) {
                    foreach ($tr['filecontracts'] as $scoid => $sco) {
                        $hash_check = $scoid;
                        if ($hash_check == $request->input('hash')) {
                            $sco['id'] = $scoid;
                            $sco['transaction'] = $trid;
                            $response[$height]['transactions'][$trid]['filecontracts'][$scoid] = $sco;
                        }
                    }

                    foreach ($tr['filecontractrevisions'] as $scoid => $sco) {
                        $hash_check = $scoid;
                        if ($hash_check == $request->input('hash')) {
                            $sco['id'] = $scoid;
                            $sco['transaction'] = $trid;
                            $response[$height]['transactions'][$trid]['filecontractrevisions'][$scoid] = $sco;
                        }
                    }

                    foreach ($tr['siacoininputs'] as $scoid => $sco) {
                        $hash_check = $scoid;
                        if ($hash_check == $request->input('hash')) {
                            $sco['id'] = $scoid;
                            $sco['transaction'] = $trid;
                            $response[$height]['transactions'][$trid]['siacoininputs'][$scoid] = $sco;
                        }
                    }

                    foreach ($tr['siacoinoutputs'] as $scoid => $sco) {
                        $hash_check = ($request->input('type') == "unlockhash") ? $sco['unlockhash']:$scoid;
                        if ($hash_check == $request->input('hash')) {
                            $sco['id'] = $scoid;
                            $sco['transaction'] = $trid;
                            $response[$height]['transactions'][$trid]['siacoinoutputs'][$scoid] = $sco;
                        }
                    }

                    foreach ($tr['siafundinputs'] as $scoid => $sco) {
                        $hash_check = $scoid;
                        if ($hash_check == $request->input('hash')) {
                            $sco['id'] = $scoid;
                            $sco['transaction'] = $trid;
                            $response[$height]['transactions'][$trid]['siafundinputs'][$scoid] = $sco;
                        }
                    }

                    foreach ($tr['siafundoutputs'] as $scoid => $sco) {
                        $hash_check = ($request->input('type') == "unlockhash") ? $sco['unlockhash']:$scoid;
                        if ($hash_check == $request->input('hash')) {
                            $sco['id'] = $scoid;
                            $sco['transaction'] = $trid;
                            $response[$height]['transactions'][$trid]['siafundoutputs'][$scoid] = $sco;
                        }
                    }
                }
                break;

                case 'transactionid':
                foreach ($block['transactions'] as $trid => $tr) {
                    if ($trid == $request->input('hash')) {
                        foreach ($tr['siacoininputs'] as $scoid => $sco) {
                            $sco['id'] = $scoid;
                            $sco['transaction'] = $trid;
                            $response[$height]['transactions'][$trid]['siacoininputs'][$scoid] = $sco;
                        }

                        foreach ($tr['siacoinoutputs'] as $scoid => $sco) {
                            $sco['id'] = $scoid;
                            $sco['transaction'] = $trid;
                            $response[$height]['transactions'][$trid]['siacoinoutputs'][$scoid] = $sco;
                        }

                        foreach ($tr['siafundinputs'] as $scoid => $sco) {
                            $sco['id'] = $scoid;
                            $sco['transaction'] = $trid;
                            $response[$height]['transactions'][$trid]['siafundinputs'][$scoid] = $sco;
                        }

                        foreach ($tr['siafundoutputs'] as $scoid => $sco) {
                            $sco['id'] = $scoid;
                            $sco['transaction'] = $trid;
                            $response[$height]['transactions'][$trid]['siafundoutputs'][$scoid] = $sco;
                        }
                    }
                }
                break;

                default:
                    $response[] = $block;
                break;
            }
        }

        return response()->json($response);
    }

    /**
     * @SWG\Get(
     *     path="/api/hash/{hash}",
     *     summary="Get hash info",
     *     @SWG\Parameter(
     *         name="hash",
     *         in="path",
     *         description="Hash transaction id, output/input id etc...",
     *         required=true,
     *         type="string",
     *     ),
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return hash info with blocks"),
     *     @SWG\Response(response="422", description="Hash required, must be alpha-numeric 64-100 length")
     * )
     */
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
        $tr = Hash::with('blocks')
                      ->join('block_hash_index', 'block_hash_index.hash_id', '=', 'hashes.id')
                      ->where('type', 'transactionid')
                      ->orderBy('hashes.id', 'desc')
                      ->take(20)
                      ->get();

        $uh = Hash::with('blocks')
                      ->join('block_hash_index', 'block_hash_index.hash_id', '=', 'hashes.id')
                      ->where('type', 'unlockhash')
                      ->orderBy('hashes.id', 'desc')
                      ->take(20)
                      ->get();

        $sc = Hash::with('blocks')
                      ->join('block_hash_index', 'block_hash_index.hash_id', '=', 'hashes.id')
                      ->where('type', 'siacoinoutputid')
                      ->orderBy('hashes.id', 'desc')
                      ->take(20)
                      ->get();

        $fc = Hash::with('blocks')
                      ->join('block_hash_index', 'block_hash_index.hash_id', '=', 'hashes.id')
                      ->where('type', 'filecontractid')
                      ->orderBy('hashes.id', 'desc')
                      ->take(20)
                      ->get();

        $sf = Hash::with('blocks')
                      ->join('block_hash_index', 'block_hash_index.hash_id', '=', 'hashes.id')
                      ->where('type', 'siafundoutputid')
                      ->orderBy('hashes.id', 'desc')
                      ->take(20)
                      ->get();

        $latest = array_merge($tr->toArray(), $uh->toArray(), $sc->toArray(), $fc->toArray(), $sf->toArray());
        return response()->json($latest);
    }

    /**
     * @SWG\Get(
     *     path="/api/search/{hash}",
     *     summary="Search hash",
     *     @SWG\Parameter(
     *         name="hash",
     *         in="path",
     *         description="Hash transaction id, output/input id etc...",
     *         required=true,
     *         type="string",
     *     ),
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return hash info with blocks"),
     *     @SWG\Response(response="422", description="Hash required, must be alpha-numeric 64-100 length")
     * )
     */
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
