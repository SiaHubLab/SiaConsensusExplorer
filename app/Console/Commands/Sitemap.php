<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App;
use App\Hash;
use App\HashType;

class Sitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // create new sitemap object
    	$sitemap = App::make('sitemap');

        // counters
        $counter = 0;
        $sitemapCounter = 0;

    	// get all products from db (or wherever you store them)
    	$hashes = Hash::chunk(500000, function($hashes) use (&$counter, &$sitemapCounter, &$sitemap) {

        	// add every product to multiple sitemaps with one sitemap index
        	foreach ($hashes as $hash) {
        		if ($counter == 50000) {
        			// generate new sitemap file
        			$sitemap->store('xml', 'sitemap-' . $sitemapCounter);
        			// add the file to the sitemaps array
        			$sitemap->addSitemap(secure_url('sitemap-' . $sitemapCounter . '.xml'));
        			// reset items array (clear memory)
        			$sitemap->model->resetItems();
        			// reset the counter
        			$counter = 0;
        			// count generated sitemap
        			$sitemapCounter++;
        		}

        		// add product to items array
        		$sitemap->add(secure_url('/hash/'.$hash->hash));
        		// count number of elements
        		$counter++;
        	}
        });



    	// you need to check for unused items
    	if (!empty($sitemap->model->getItems())) {
    		// generate sitemap with last items
    		$sitemap->store('xml', 'sitemap-' . $sitemapCounter);
    		// add sitemap to sitemaps array
    		$sitemap->addSitemap(secure_url('sitemap-' . $sitemapCounter . '.xml'));
    		// reset items array
    		$sitemap->model->resetItems();
    	}

    	// generate new sitemapindex that will contain all generated sitemaps above
    	$sitemap->store('sitemapindex', 'sitemap');
    }
}
