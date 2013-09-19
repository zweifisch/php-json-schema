<?php

use \jsonschema\Validator;

class ValidatorTest extends PHPUnit_Framework_TestCase
{
	public function test()
	{
	}

	public function setup()
	{
		if(!isset($this->validator))
		{
			$this->validator = new Validator;
		}
	}

}
