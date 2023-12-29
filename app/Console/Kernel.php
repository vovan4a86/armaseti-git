<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		Commands\GremirProducts::class,
		Commands\RidanProducts::class,
		Commands\AdlProducts::class,
		Commands\VandjordProducts::class,
		Commands\WellMixProducts::class,
		Commands\AsteamaProducts::class,
		Commands\RosmaProducts::class,
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
