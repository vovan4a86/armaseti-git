<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Fanky\Crm\Models\Task;
use Fanky\Crm\Mailer;
use DB;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'App\Console\Commands\Inspire',
		'App\Console\Commands\Test',
        'App\Console\Commands\CableProducts',
        'App\Console\Commands\CableSystems',
        'App\Console\Commands\Uteplitel',
        'App\Console\Commands\Vodostok',
        'App\Console\Commands\SnowHolder',
        'App\Console\Commands\Tubes',
        'App\Console\Commands\Lights',
        'App\Console\Commands\Arenda',
		Commands\ImportOld::class,
		Commands\SitemapCommand::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{

        $schedule->command('sitemap')->dailyAt('01:15');
	}
	//в крон прописать - php artisan schedule:run
}
