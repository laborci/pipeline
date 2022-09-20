<?php namespace Atomino2\Mercury\Middleware;

use Atomino2\Mercury\RequestHandlerErrorException;
use Symfony\Component\HttpFoundation\Response;

class CatchException extends AbstractMiddleware {
	public static function setup(bool|array $verbose = false, bool $throw = true) { return parent::setup(get_defined_vars()); }
	protected function handle(): Response|null {
		try {
			return $this->next();
		} catch (\Throwable $exception) {
			$verbose = $this->arg("verbose");

			if ($exception instanceof RequestHandlerErrorException) {
				$verbose = true;
			} elseif (is_array($verbose)) {
				$classes = $verbose;
				$verbose = false;
				foreach ($classes as $class) {
					if ($exception instanceof $class || is_subclass_of($exception, $class)) {
						$verbose = true;
						break;
					}
				}
			}
			if ($verbose) {
				if ($exception instanceof RequestHandlerErrorException) {
					$errorMessage = $exception->errorMessage;
					$errorType = $exception->errorType;
					$errorCode = $exception->errorCode;
					$statusCode = $exception->statusCode;
				} else {
					$statusCode = 500;
					$errorMessage = $exception->getMessage();
					$errorCode = $exception->getCode();
					$errorType = get_class($exception);
				}
				$this->sendErrorMessage($statusCode, $errorMessage, $errorType, $errorCode);
			} else {
				$this->sendErrorMessage();
			}
			if ($this->arg("throw") && !($exception instanceof RequestHandlerErrorException)) throw $exception;
			else die();
		}
	}

	protected function sendErrorMessage(int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, string $errorMessage = "", string $errorType = "", string $errorCode = "") {
		$request = $this->ctx("original-request");
		$request = $request ?: $this->ctx("request");
		$message = [
			"status"  => [
				"code"    => $statusCode,
				"message" => Response::$statusTexts[$statusCode],
			],
			"request" => [
				"method" => $request->getMethod(),
				"uri"    => $request->getSchemeAndHttpHost() . $request->getPathInfo(),
			],
		];
		if ($errorMessage || $errorType || $errorCode) $message["error"] = [];
		if ($errorCode) $message["error"]["code"] = $errorCode;
		if ($errorType) $message["error"]["type"] = $errorType;
		if ($errorMessage) $message["error"]["message"] = $errorMessage;
		(new Response(json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $statusCode, ["Content-Type" => "application-json"]))->send();
	}
}	