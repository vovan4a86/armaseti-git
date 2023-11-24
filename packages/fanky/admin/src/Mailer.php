<?php namespace Fanky\Admin;

use Swift_Message;
use Illuminate\Mail\Message;
use App;

Class Mailer
{
	private static $ses_url = 'http://work.fanky.ru/mail/';

	private $message;
	private $views;

	private $type;

	const NOTIFICATION = 'notification';
	const DELIVERY = 'delivery';

	public function __construct()
	{
		$this->views = App::make('Illuminate\Contracts\View\Factory');
		$this->message = new Message(new Swift_Message);
	}

	public static function sendNotification($view, $data = array(), $fun)
	{
		$mailer = new self;
		$mailer->type = self::NOTIFICATION;
		$fun($mailer->message);
		$data['message'] = $mailer->message;
		$view = $mailer->views->make($view, $data)->render();
		$mailer->message->setBody($view, 'text/html');
		return $mailer->sendFanky();
	}

	public static function sendDelivery($view, $data = array(), $fun)
	{
		$mailer = new self;
		$mailer->type = self::DELIVERY;
		$fun($mailer->message);
		$data['message'] = $mailer->message;
		$view = $mailer->views->make($view, $data)->render();
		$mailer->message->setBody($view, 'text/html');
		return $mailer->sendFanky();
	}

	public function sendFanky()
	{
		$message = $this->message->getSwiftMessage();

		$to = array_keys($message->getTo());

		if ($this->type != 'notification') {
			$message->setTo('mailer@mailer.ru');
		}

		$body = $message->toString();

		$from = array_keys($message->getFrom());

		$data = array(
			'type'   => $this->type,
			'domain' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : env('BASE_URL'),
			'to'     => $to,
			'body'   => $body
		);
		//инициализируем сеанс
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, self::$ses_url);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'data=' . urlencode(json_encode($data)));
		$res = curl_exec($curl);
		curl_close($curl);

		return $res;
	}
}
