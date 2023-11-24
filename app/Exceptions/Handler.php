<?php namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		'Symfony\Component\HttpKernel\Exception\HttpException'
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  Throwable $e
	 * @return void
	 */
	public function report(Throwable $e) {
		return parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  Throwable $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Throwable $e) {
		if (method_exists($e, 'getStatusCode')) {
			$status = $e->getStatusCode();
			if ($status == '404') {
//				\View::share('title', 'Страница не найдена');
                return response()->view('errors.404', [], 404);
			}
		}
		return parent::render($request, $e);
	}

}
