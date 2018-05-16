<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class MinerPreload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miner:preload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Some hardcoded miner detection';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Client([]);

        $res = $client->request('GET', 'https://explorer.siahub.info/api/consensus/');
        $consensus = json_decode($res->getBody(), true);

        $last_block = $consensus['last_indexed_height'];

        $requests = function () use ($last_block) {
            for($i=$last_block-10;$i<$last_block;$i++){
                yield new Request('GET', 'https://explorer.siahub.info/api/block/'.$i);
            }
        };

        $pool = new Pool($client, $requests(), [
            'concurrency' => 20,
            'fulfilled' => function ($response) use ($client) {
                //echo 'Completed request '.$index.PHP_EOL;

                $block = json_decode($response->getBody(), true);

                var_dump($block['blockheight']);
                var_dump(end($block['minerpayouts'])['unlockhash']);

                $res = $client->request('GET', 'https://explorer.siahub.info/api/miner/'.end($block['minerpayouts'])['unlockhash'].'/'.$block['blockheight']);
                $pool = json_decode($res->getBody(), true);
                var_dump($pool);
            },
            'rejected' => function ($reason, $index) {
            },
        ]);
        $promise = $pool->promise();
        $promise->wait();
    }
}
