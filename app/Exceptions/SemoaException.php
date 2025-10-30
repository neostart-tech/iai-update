<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class SemoaException extends Exception
{
	protected $message;
	protected $code;
	protected Throwable $previous;

	public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->message = $message;
		$this->code = $code;
		$this->previous = $previous;
	}


}
