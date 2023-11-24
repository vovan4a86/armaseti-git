<?php namespace App\Console\Commands;

use SiteHelper;
use Illuminate\Console\Command;
use Mail;

class Test extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'test';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Display an inspiring quote';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		Mail::queue('mail.feedback', [], function($message){
			/** @var \Swift_Message $message */
			$message->to('as@klee.ru')
				->subject('Тестовое уведомление')
				->from('info@metallresurs.ru');
			SiteHelper::signMessage($message);
		});

        return 0;
	}

}
