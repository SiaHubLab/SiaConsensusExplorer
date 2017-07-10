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

            if (env('APP_ENV') == "local") {
                $response[$height]['raw'] = $block;
            }

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
                        $sco['id'] = $scoid;
                        $sco['transaction'] = $trid;
                        if ($hash_check == $request->input('hash')) {
                            $response[$height]['transactions'][$trid]['filecontracts'][$scoid] = $sco;
                        }

                        foreach ($sco['validproofoutputs'] as $hash => $proof) {
                            $hash_check = ($request->input('type') == "unlockhash") ? $proof['unlockhash']:$hash;
                            if ($hash_check == $request->input('hash')) {
                                $response[$height]['transactions'][$trid]['filecontracts'][$scoid] = $sco;
                            }
                        }

                        foreach ($sco['missedproofoutputs'] as $hash => $proof) {
                            $hash_check = ($request->input('type') == "unlockhash") ? $proof['unlockhash']:$hash;
                            if ($hash_check == $request->input('hash')) {
                                $response[$height]['transactions'][$trid]['filecontracts'][$scoid] = $sco;
                            }
                        }
                    }

                    foreach ($tr['filecontractrevisions'] as $scoid => $sco) {
                        $hash_check = $scoid;
                        $sco['id'] = $scoid;
                        $sco['transaction'] = $trid;
                        if ($hash_check == $request->input('hash')) {
                            $response[$height]['transactions'][$trid]['filecontractrevisions'][$scoid] = $sco;
                        }

                        foreach ($sco['newvalidproofoutputs'] as $hash => $proof) {
                            $hash_check = ($request->input('type') == "unlockhash") ? $proof['unlockhash']:$hash;
                            if ($hash_check == $request->input('hash')) {
                                $response[$height]['transactions'][$trid]['filecontractrevisions'][$scoid] = $sco;
                            }
                        }

                        foreach ($sco['newmissedproofoutputs'] as $hash => $proof) {
                            $hash_check = ($request->input('type') == "unlockhash") ? $proof['unlockhash']:$hash;
                            if ($hash_check == $request->input('hash')) {
                                $response[$height]['transactions'][$trid]['filecontractrevisions'][$scoid] = $sco;
                            }
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
            $hash = Hash::with(['blocks', 'proofs', 'contracts'])
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
