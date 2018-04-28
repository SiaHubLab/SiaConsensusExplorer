<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Miner;
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
            'height'=> 'required|numeric',
        ]);

        $cache_key = "block_".$height;
        if (!Cache::has($cache_key)) {
            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->request('GET', env('SIA_ADDRESS').'/consensus/blocks/'.$height);
                $block = json_decode($res->getBody(), true);

                $block_hash = Hash::with(['miner'])
                                  ->join('block_hash_index', 'block_hash_index.hash_id', '=', 'hashes.id')
                                  ->where('type', 'blockid')
                                  ->where('block_hash_index.height', $height)
                                  ->first();
                $block = array_merge($block, ['hash_data' => $block_hash]);
                Cache::put($cache_key, $block, 60*24);
            } catch (\Exception $e) {
                Cache::put($cache_key, ['error' => 'Block is not ready yet.'], 5);
                return response()->json(['error'=> 'Ooops, our wallet unavailable. Please try later.'], 503);
            }
        }

        $block = Cache::get($cache_key);

        if(!empty($block['error'])) {
              return response()->json($block, 503);
        }

        return response()->json($block);
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
            'blocks'=> 'required',
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
                    Cache::put($cache_key, $block, 60*24);
                } catch (\Exception $e) {
                    //return response()->json(['error'=> 'Ooops, our wallet unavailable. Please try later.'], 503);
                    continue;
                }
            } else {
                $block = Cache::get($cache_key);
                if(!empty($block['error'])) { continue; }
            }


            $response_cache_key = "resp_block_".$height.$request->input('type').$request->input('hash');
            if (Cache::has($response_cache_key)) {
                $response[$height] = Cache::get($response_cache_key);
                $response[$height]['cached'] = true;
            } else {
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
                            $hash_check = ($request->input('type') == "unlockhash") ? $sco['unlockhash']:$scoid;
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

                Cache::put($response_cache_key, $response[$height], 60*24);
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
            'hash'=> 'required|alpha_num|between:64,100',
        ]);

        $cache_key = "hash_".$hash;
        if (!Cache::has($cache_key)) {
            $hash = Hash::with([
                                'blocks'=> function($q){
                                    $q->limit(10000)->orderBy('height', 'desc');
                                },
                                'proofs',
                                'contracts'
                        ])
                        ->where('hash', $hash)
                        ->first();
            Cache::put($cache_key, $hash, 10);
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
            'hash'=> 'required|alpha_num|between:64,100',
        ]);

        $searchResults = Hash::with('blocks')
                      ->where('hash', $search)
                      ->take(50)
                      ->get();


        return response()->json($searchResults);
    }

    /**
     * Trying to detect who mined this block
     *
     * @return return json
     */
    public function miner($hash, $block) {
        $miners = [
            'Luxor.tech'=> ['cb0e0bc4536efed858614252f09fbe76365cf7201336e779249a654bab1a3bbadc7e68cd4f21'],
            'F2pool.com'=> ['dc0cb4f6cd8003ebf3d86cf0d9a5bbdf1e517c243238d26fd46709835a59c87338a380992d84'],
            'Nanopool.org'=> [
                '02dcb4b56a9bdc8b8423006664a5bdd4b700c69b505bd63c35a71dce7d0adc35447795c79582',
                '07d3d5c4d05d689255e3fcef40254111fa01e12ad9852d7313e166d94af5905906ed2c054c76',
                '0de2bfb0e65a87fafaee8c8d6f8a896bac2607cd120519817494cf2562b930d383634c8f4c61',
                '1b419128d62996eb53e91503593e0891920ba4311438fe908e4cfbd1aab7a3674a6c3c110e9f',
                '1ebc2e0bd97244ef75644c9b097284682d4b2a1524330ad379d30501fa22aab0222947700371',
                '22019362651335ac1d156b8b624a7257cd1854361a32ecd62e15d8930bb6d7f0467b66380a2e',
                '270a6d1dba569a236872babd1c9772366a4fd93c27603a882446eb97d63852d78af36c794c95',
                '27f6943106db14e06de7e9e9d3ae8b852f380bdb53848e805ff67a1032662dd1b59ec54cf1ee',
                '2fa9b23a8fd9e233bffef7efd106deb4b308cc26c64a6a6ed4e64f6904e5bbe1a0877029bcf5',
                '3073ad1c6ce6ff9b13d79a6b1798b776fa06511104e59c32f3b5ee430afd5aabd08f88f75f03',
                '46234b4a1630e2b0d3f27fad41dcb809fddd2bbcd5f2ddfc8776a9fe5472337bd105313855d0',
                '46d57ca5a5e630da8359d3679013d987179356b145b0f80cc64934656d74e972e371121d2e40',
                '48fb48001de2724004650810db1bb50acff668926923b1e568ef48a01484d6d786fad8108f03',
                '4cda0b82b41fa6c98cf0295a4d6fe51f233e9fdd8d5f9a89c9f79aba2867c4094b2b304794c6',
                '56178cc096fd058831b4be7d78b491e05c709a263e5f735023ce86931bafc4c80cd4d170e989',
                '5f2fb0e0fe47dbc2463f5df379ac86adc9a0665684d0e07927858f37982c231525620ab3607d',
                '626bafd4f9e607cee379c94f8f2fe13cbd39b1c854b2218e7e82a72d725f0d364b7c698f83b5',
                '6b6bfc7063b18d52fd07c0ac989e79a0188d6f063fb13c3f8c5fb3298ab630b3fe449ebc3b30',
                '74449fb4cd648879584e940484a00fb7492090206a9e3dab2b3ee2ed6cce479d74700328b75e',
                '82e362e274b36406136cf2564314a680834d17e1728368ffb0f2a7424837224b84f85578f7ec',
                '83490fb377e9f67a562deb7fd868b8c1e2df0d5d3ce184df129018e7a23a80063188bcc20c4e',
                '89b773a078ce3cebf857290e1cc4033f2fe2f17ed1de644a1a482d56473757adc903843c7552',
                '8fb1088b76f078967877ae2a1103c73b250f98ad3fad6b077883453a68e0f42ac3d0fb60f35d',
                '94103f06d5d2ac816d22b05da9607a86d44029252b8b94f92e6e9f1a183009fc52ac533918b3',
                '9af0a78a2f2ad59a03763357650cd1619e552ded9189eef1bcc6d2c5301a018eec02b71a1479',
                '9d6d0f69fcb8f0cf264339459ba1b7f3cc65e03378abe1380a67be1bc88b886c1e905ea0428a',
                '9d79c1be89e528f45cd7020ae71c65fab3add38985751ab46e7a81c914b548f5d3e95daa73cd',
                'a8efe54c828dbd5b504064a28a750428a266d1f234657415b623dae40af96f580a11e8ce24a8',
                'aa8f667aaab94ed1a1bad7a9a9c436886fff43aacc7f899f1fc6e815e8d419d01df559629aa1',
                'adb0862ec0a9154d8be2df0b2338947f78398fc38d0b096005d71061e2987842e83143435b96',
                'ae2a71e27649242b181c9c505cc0f6637f902e4ef6907c73b3aa7dd8620ac999af5acc1d8238',
                'b169bb9fb54a06521b65f491a0e79e69fea7fd937bc9bb91e8ceae956bdb65123f4edb0632ed',
                'b43d9930e7f5bfdb42bb8a83982eed9d6b4822f8e885807510462ae5c55a50df9a336a801110',
                'b5180dc32ecc8c32ed25f5596b164f34df637520a62a9a21879a78835244f547e18f7107da3d',
                'b989c918bb694a09a5c2ce9f65eefa0c80d4ac553c095dc465083723ce189de3d62a8c4dd8ee',
                'ca27aad88e933bc1277352c0f5e71ddd51a59ac29d22a40389bcc597cb3edb9610a7a0084909',
                'cadfea5ff54e4f0d0a8157b17c6cd2e5842398ba2ed77a3559208e1ad30ba2e795a1fd104af1',
                'cb82bdb61b205401bda6072674e12e76a92794e30cc697eb970841fb30c134f14ab4308d1fd7',
                'd0ad5f5eb53512d7ace2adec268af2b55417bc1fbd28ebfd0c1ccc12c7ebf4e64777e4273491',
                'd6b14a1e056180af2b2d921285ac046e088e12262419f759ab3089d2825c21192eaf04ca450c',
                'da950a79a9e4c3490aa8d75b34fcb65e8dd87c9b78218028dcd6783fc71d14be8f451b76b924',
                'e26abe55dfb3dab9b4dfa65f42f25abbc05820c4eca50b58c1a538103518746a6c83aa496b54',
                'e3a5fd2b581375bc0b6df7837f5a2acf867f1f0111e64f876504b25b8c3dbed96b021e519af1',
                'f4858920ae77813216f9c7dbc9e9c8fa69c17f9e31e1384dc1004dbd8f3995458c9c95d95030',
                'f7341cd667532a0829af4911ad0deeb706273240443d467d85b0d8d30f76d7b2a6b74f9a0266',
                'f79c44bc9c9eeece81ac6c26ff128efb6a01bb979360d667a2018851b9d2e746b35e88cb45a1',
                'f8a4531998711db0cd6b75533af61eaa31d84309f1424d6bd6e83cd8e516412b0aab7f35f98b',
                'fc9753a4a3518e3d8e2b1be73e58437951699326a91b932c3a68254c3ccacda006078c54c00d',
                'ff300aee1228117949ee9d2f74d404e6695e4714c570bb525c80c34659679257306fd195b0f3',
                '00b7f68becc0897880a0129f4e2768fd8d87bcf6c633f1eecabf35ddbb92f7653a64a364617d',
                '32ee4a0d3619d9b92d3b0cdce1513179bca68c0f3512a1512a4992263a570101afae937056f7',
                '47e5f4590e005b7bac96e97e05fc6c5ee183b0a170dd7a8a27283b81f3102a4e0ece7f954ea1',
                'bbe417a16f025090b700dcc2960639254a9fe12bde10a7f59135678aae5d3a5fa31499c4406a',
                'e4b6f0f28e8b6e855e1e5c556e6e91ef31d755333e6f94b97f0a63054ae9004df977dd66ae52',
                '1b287cb241d09a3d285b8557ec0e4d703525608ccd824ab0909541f788796005969582a75053',
                'c68909c2bca2433354bdc145a5a3074f3627c98569a6ac16029c1c0877652dd0f0284a3f883c',
                'c25b97d00106218ae92b7cad8802f4b07b9581059dec47e5193a994643dd8f1f863b1d23762e',
                'c7c9b472f1111cc390d000dae2551e052c59e11fda16986903c485b68b33d9403f46bcd5a51c',
                '2e4611eab719fe34bed7c0f3e90d3c68d7e1ffc4fb2cc856d4c37c8210d044972bb92d2668d9',
                'c18fc44c21b4dfdba61b34731ad85f7161d4a3f50247103d96e664a63c84e21cd274c5765ed6',
                '754828bde58d72a3cd6bf717ea29f810fe69edd429f4cf162c693b2b8e0d7dda204eed7cb267',
                '0cc9dc1fd12ca5f265ef2452c81f51db435bec4518ec1a6ec778ec3760ad6ceb2033bca4328c',
                '1c8304a2075c9657bdad408568e9a63323890e90cd5e9be87c517cf286cf55f82a14ac5759a7',
                '996dde0efdfde92b1bf194a3cd430a9030f1eabb8738b452e5345179e088f39efa42d4ed2aa6',
                '0f2169b23834cca71978211a2e180d8b1dcfbba984af81bc924d0ab8786641458db82ec56fb1',
                'cf6b15e5ffb57f0a8e50e8f1f62aa7efc81229da93e694569321b28b0d0d519b76da06376ef9',
                'da4dbf26a4b22f6cf77f65d60e66306337cf21dd7dd7687975c28bd31f7ffa6462398da6d5df',
                'f387ec7f3e97e9915cd58bbf711dd7dc1cbd3f117bee9b1a8349b6a85bf7b2bcec7500ea443c',
                'f7341cd667532a0829af4911ad0deeb706273240443d467d85b0d8d30f76d7b2a6b74f9a0266',
                '6becf54cfa6f677305fe49769370552c2829554274497d04a8e22c4407803a3541e5825a43f6',
                'b27089bff7743f68a54dfd6464af37427bf2cfed19006187ca50d4e44956b80d18505d00d235',
                'ff4942024160d759d2f128cdeca7b2ea184ba80208b29a27ddaa14e6ce14bd0f535869ed37d1'
            ],
            'Siamining.com'=> [
                'e0e00caf28477a4ac57125ee94055991a9f2d10e57073034b1de2b70a6b370063c099ab9d861',
                '0ba629b7a0b00132e5b7b32557f4d956854d1da3703d0306ae648c44d44990316a32ebf24418',
                'd1c5b364f5f5dab1005aa14b44ef1bf743683ea85221ca6e1a1b0c7a8c64ffe95cc253297d8a',
                'd7775e8d120843a6cd64d3b50784962912bea16d260b7fc9e4861cbc3f8ffca3704cc357b49b',
                '8374a8051c523e8563332cd6d44e1151a689f0613aaa9ed3b561db4bf6fb09f1a62531a457ce',
            ],
        ];

        $miner = false;
        $cache_key = "mphblocks";
        if (!Cache::has($cache_key)) {
            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->request('GET', 'https://siastats.info/dbs/mphblocks.json'); // Hello SiaStats
                $block = json_decode($res->getBody(), true);
                Cache::put($cache_key, $block, 10);
            } catch (\Exception $e) {
                //return response()->json(['error'=> 'Ooops, mphblocks unavailable. Please try later.'], 503);
            }
        }

        if($mph = Cache::get($cache_key)) {
            if(in_array($block, $mph)) {
                $miner = "MiningPoolHub";
            }
        }

        $cache_key = "nanoblocks";
        if (!Cache::has($cache_key)) {
            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->request('GET', 'https://siastats.info/dbs/nanopoolblocks.json'); // Hello SiaStats
                $block = json_decode($res->getBody(), true);
                Cache::put($cache_key, $block, 10);
            } catch (\Exception $e) {
                //return response()->json(['error'=> 'Ooops, mphblocks unavailable. Please try later.'], 503);
            }
        }

        if($nano = Cache::get($cache_key)) {
            if(in_array($block, $nano)) {
                $miner = "Nanopool.org";
            }
        }

        $cache_key = "antblocks";
        if (!Cache::has($cache_key)) {
            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->request('GET', 'https://siastats.info/dbs/antpoolblocks.json'); // Hello SiaStats
                $block = json_decode($res->getBody(), true);
                Cache::put($cache_key, $block, 10);
            } catch (\Exception $e) {
                //return response()->json(['error'=> 'Ooops, mphblocks unavailable. Please try later.'], 503);
            }
        }

        if($nano = Cache::get($cache_key)) {
            if(in_array($block, $nano)) {
                $miner = "AntPool.com";
            }
        }

        if(!$miner) {
            foreach($miners as $pool => $addresses) {
                if(in_array($hash, $addresses)) {
                    $miner = $pool;
                    break;
                }
            }
        }

        if($miner) {
            $block_hash = Hash::join('block_hash_index', 'block_hash_index.hash_id', '=', 'hashes.id')
                              ->where('type', 'blockid')
                              ->where('block_hash_index.height', $block)
                              ->first();
            if($block_hash && !$block_hash->miner_id) {
                $block_hash->miner_id = Miner::firstOrCreate(['name' => $miner])->id;
                $block_hash->save();

                $cache_key = "block_" . $block;
                Cache::delete($cache_key);
            }
        }

        return response()->json(['miner' => $miner]);
    }

    public function getBlocksDistribution($blocks)
    {
        $blocks = ($blocks > 1000) ? 1000:$blocks;

        $cache_key = "getBlocksDistribution".$blocks;
        if (!Cache::has($cache_key)) {
            $distribution = Hash::with('miner')
                          ->selectRaw('miner_id')
                          ->where('type', 'blockid')
                          ->orderBy('hashes.id', 'desc')
                          ->take($blocks)
                          ->get();
            $data = [];
            foreach($distribution as $miner) {
                $miner_id = ($miner->miner_id) ? $miner->miner_id:'Unknown';
                if(!isset($data[$miner_id])) {
                    $data[$miner_id] = ['miner' => $miner->miner, 'blocks' => 0];
                }

                $data[$miner_id]['blocks']++;
            }



            usort($data,function($a, $b) {
                return $b['blocks'] <=> $a['blocks'];
            });

            Cache::put($cache_key, $data, 60);
        }

        return response()->json(Cache::get($cache_key));
    }
}
