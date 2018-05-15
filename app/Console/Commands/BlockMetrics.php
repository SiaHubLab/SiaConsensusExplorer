<?php

namespace App\Console\Commands;

use App\BlockMetric;
use Carbon\Carbon;
use Illuminate\Console\Command;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use DB;

class BlockMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blockmetrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record block facts to DB';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::table('stats')->whereRaw('created_at <= date_sub(now(), interval 2 day)')->delete();

        $client = new Client();

        $last_height = DB::table('block_hash_index')->select('height')->orderBy('height', 'desc')->first();
        $this->info('Current height: '.$last_height->height);

        $last_metric = BlockMetric::orderBy('height', 'desc')->first();
        if($last_metric) {
            $start_height = $last_metric->height;
        } else {
            $start_height = $last_height->height-1000;
        }

        $this->info('Start height: '.$start_height);

        $requests = function () use($start_height, $last_height) {
            for($i=$start_height; $i<=$last_height->height; $i++){
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
                var_dump($block['blockheader']);


                $metrics = new BlockMetric();
                $metrics->height = $block['blockheight'];
                $metrics->difficulty = $block['difficulty'];
                $metrics->estimatedhashrate = $block['estimatedhashrate'];
                $metrics->timestamp = Carbon::createFromTimestamp($block['blockheader']['timestamp']);
                $metrics->transactions = count($block['transactions']);
                $metrics->new_file_contracts = 0;
                $metrics->revisioned_file_contracts = 0;
                foreach($block['transactions'] as $tx) {
                    $metrics->new_file_contracts += count($tx['filecontracts']);
                    $metrics->revisioned_file_contracts += count($tx['filecontractrevisions']);
                }

                try {
                    $metrics->save();
                } catch(\Exception $e) {

                }
            },
            'rejected' => function ($reason, $index) {
            },
        ]);
        $promise = $pool->promise();
        $promise->wait();
    }
}
