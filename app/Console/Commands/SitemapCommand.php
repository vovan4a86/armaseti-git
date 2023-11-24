<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SiteHelper;

class SitemapCommand extends Command {
    protected $signature = 'sitemap';

    protected $description = 'Command description';

    public function handle(): void {
        SiteHelper::generateSitemap();
    }
}
