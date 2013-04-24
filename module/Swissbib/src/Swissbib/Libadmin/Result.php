<?php
namespace Swissbib\Libadmin;

/**
 * [Description]
 *
 */
class Result
{

	protected $success = true;

	protected $errors = array();

	protected $messages = array();



	public function addError($errorMessage)
	{
		$this->errors[] = $errorMessage;
		$this->success  = false;

		return $this;
	}



	public function addMessage($message)
	{
		$this->messages[] = $message;

		return $this;
	}



	public function getErrors()
	{
		return $this->errors;
	}



	public function getMessages()
	{
		return $this->messages;
	}



	public function hasMessages()
	{
		return sizeof($this->messages) > 0;
	}



	public function hasErrors()
	{
		return sizeof($this->errors) > 0;
	}



	public function isSuccess()
	{
		return $this->success;
	}

}
